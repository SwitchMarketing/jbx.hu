<?php

namespace App\Controllers;

use App\Libraries\ShopSettings;
use App\Models\ProductMasterModel;
use App\Models\ProductVariantModel;
use Config\AppConfig;

class GoogleMerchantFeed extends BaseController
{
    public function index()
    {
        $appConfig = new AppConfig();
        $rows = $this->getFeedRows();
        $attributeMap = $this->getVariantAttributeMap(array_map(static fn ($row) => (int) $row->variant_id, $rows));

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $xml->startElement('channel');
        $xml->writeElement('title', $appConfig->siteName . ' - Google Merchant feed');
        $xml->writeElement('link', base_url('/'));
        $xml->writeElement('description', 'Google Merchant termekfeed a jbx.hu shop aktiv termekeihez.');

        foreach ($rows as $row) {
            $priceData = $this->buildPriceData((float) ($row->price ?? 0), $row->discount_price ?? null);
            if ($priceData['price'] <= 0) {
                continue;
            }

            $variantAttrs = $attributeMap[(int) $row->variant_id] ?? [];
            $itemData = $this->buildItemData($row, $variantAttrs, $priceData);

            $xml->startElement('item');
            $xml->writeElement('g:id', $itemData['id']);
            $xml->writeElement('g:item_group_id', $itemData['item_group_id']);
            $xml->writeElement('title', $itemData['title']);
            $xml->writeElement('description', $itemData['description']);
            $xml->writeElement('link', $itemData['link']);
            $xml->writeElement('g:image_link', $itemData['image_link']);
            $xml->writeElement('g:availability', $itemData['availability']);
            $xml->writeElement('g:condition', 'new');
            $xml->writeElement('g:price', $itemData['price']);

            if ($itemData['sale_price'] !== null) {
                $xml->writeElement('g:sale_price', $itemData['sale_price']);
            }

            if ($itemData['brand'] !== '') {
                $xml->writeElement('g:brand', $itemData['brand']);
            }

            if ($itemData['gtin'] !== '') {
                $xml->writeElement('g:gtin', $itemData['gtin']);
            }

            if ($itemData['mpn'] !== '') {
                $xml->writeElement('g:mpn', $itemData['mpn']);
            }

            if ($itemData['gtin'] === '' && $itemData['mpn'] === '') {
                $xml->writeElement('g:identifier_exists', 'no');
            }

            $xml->writeElement('g:product_type', $itemData['product_type']);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody($xml->outputMemory());
    }

    private function getFeedRows(): array
    {
        $db = \Config\Database::connect('shop');

        return $db->table('product_variants pv')
            ->select([
                'pv.id AS variant_id',
                'pv.sku AS sku',
                'pv.slug AS variant_slug',
                'pv.price AS price',
                'pv.discount_price AS discount_price',
                'pv.state AS variant_state',
                'pm.id AS master_id',
                'pm.name AS master_name',
                'pm.slug AS master_slug',
                'pm.description AS description',
                'categories.path AS category_path',
                'categories.name AS category_name',
                '(SELECT i.filename FROM images i WHERE i.master_id = pm.id AND (i.variant_id IS NULL OR i.variant_id = pv.id) ORDER BY i.position ASC, i.id ASC LIMIT 1) AS image',
            ])
            ->join('product_masters pm', 'pm.id = pv.master_id', 'inner')
            ->join('category_tree AS categories', 'categories.unas_id = pm.category_id', 'left')
            ->where('pm.state', ProductMasterModel::STATE_ACTIVE)
            ->whereIn('pv.state', [
                ProductVariantModel::STATE_INSTOCK,
                ProductVariantModel::STATE_BACKORDER,
                ProductVariantModel::STATE_INQUIRE,
            ])
            ->orderBy('pm.id', 'ASC')
            ->orderBy('pv.position', 'ASC')
            ->orderBy('pv.id', 'ASC')
            ->get()
            ->getResult();
    }

    private function getVariantAttributeMap(array $variantIds): array
    {
        $variantIds = array_values(array_unique(array_filter(array_map('intval', $variantIds), static fn ($id) => $id > 0)));
        if (empty($variantIds)) {
            return [];
        }

        $db = \Config\Database::connect('shop');
        $rows = $db->table('variant_attribute_values vav')
            ->select('vav.variant_id, a.name AS attribute_name, vav.value')
            ->join('attributes a', 'a.id = vav.attribute_id', 'inner')
            ->whereIn('vav.variant_id', $variantIds)
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $variantId = (int) ($row->variant_id ?? 0);
            if ($variantId < 1) {
                continue;
            }

            $name = $this->normalizeAttributeName((string) ($row->attribute_name ?? ''));
            $value = trim((string) ($row->value ?? ''));
            if ($name === '' || $value === '') {
                continue;
            }

            $map[$variantId][$name] = $value;
        }

        return $map;
    }

    private function buildItemData(object $row, array $attrs, array $priceData): array
    {
        $title = trim((string) (($row->master_name ?? '') . ' ' . ($row->sku ?? '')));
        if ($title === '') {
            $title = (string) ($row->master_name ?? 'Termek');
        }

        $description = $this->normalizeDescription((string) ($row->description ?? ''));
        if ($description === '') {
            $description = (string) ($row->master_name ?? 'Termek');
        }

        $brand = $this->extractAttributeValue($attrs, ['brand', 'marka', 'gyarto', 'manufacturer']);
        if ($brand === '') {
            $brand = 'Axelent';
        }

        $gtin = $this->extractAttributeValue($attrs, ['gtin', 'ean', 'vonalkod', 'barcode', 'isbn']);
        $mpn = $this->extractAttributeValue($attrs, ['mpn', 'manufacturerpartnumber', 'cikkszam', 'partnumber']);

        return [
            'id' => (string) ($row->sku ?: ('jbx-' . (int) $row->variant_id)),
            'item_group_id' => (string) ((int) ($row->master_id ?? 0)),
            'title' => $title,
            'description' => $description,
            'link' => $this->buildProductUrl($row),
            'image_link' => $this->buildImageUrl((string) ($row->image ?? '')),
            'availability' => $this->mapAvailability((string) ($row->variant_state ?? '')),
            'price' => $this->formatGooglePrice($priceData['price']),
            'sale_price' => $priceData['sale_price'] !== null ? $this->formatGooglePrice($priceData['sale_price']) : null,
            'brand' => $brand,
            'gtin' => $gtin,
            'mpn' => $mpn,
            'product_type' => $this->buildProductType((string) ($row->category_name ?? ''), (string) ($row->category_path ?? '')),
        ];
    }

    private function buildPriceData(float $priceEurNet, $discountPriceEurNet): array
    {
        $baseNetEur = max(0.0, (float) $priceEurNet);
        $discountNetEur = is_numeric($discountPriceEurNet) ? max(0.0, (float) $discountPriceEurNet) : 0.0;

        $baseGrossHuf = shop_price_breakdown_huf_from_eur_net($baseNetEur, ShopSettings::vatRatePercent())->gross;
        $effectiveNetEur = shop_effective_net_price_eur($baseNetEur, $discountNetEur);
        $effectiveGrossHuf = shop_price_breakdown_huf_from_eur_net($effectiveNetEur, ShopSettings::vatRatePercent())->gross;

        $hasSale = $discountNetEur > 0 && $discountNetEur < $baseNetEur;

        return [
            'price' => $hasSale ? $baseGrossHuf : $effectiveGrossHuf,
            'sale_price' => $hasSale ? $effectiveGrossHuf : null,
        ];
    }

    private function buildProductUrl(object $row): string
    {
        $segments = ['termekek'];

        $categoryPath = trim((string) ($row->category_path ?? ''), '/');
        if ($categoryPath !== '') {
            foreach (explode('/', $categoryPath) as $segment) {
                $segment = trim((string) $segment);
                if ($segment !== '') {
                    $segments[] = $segment;
                }
            }
        }

        $masterSlug = trim((string) ($row->master_slug ?? ''), '/');
        $variantSlug = trim((string) ($row->variant_slug ?? ''), '/');

        if ($masterSlug !== '') {
            $segments[] = $masterSlug;
        }
        if ($variantSlug !== '') {
            $segments[] = $variantSlug;
        }

        return base_url(implode('/', $segments));
    }

    private function buildImageUrl(string $filename): string
    {
        $filename = trim($filename);
        if ($filename === '') {
            return 'https://placehold.co/1200x1200';
        }

        if (preg_match('#^https?://#i', $filename)) {
            return $filename;
        }

        return base_url('imgs/products/' . ltrim($filename, '/'));
    }

    private function mapAvailability(string $state): string
    {
        $state = strtolower(trim($state));

        if ($state === ProductVariantModel::STATE_INSTOCK) {
            return 'in_stock';
        }

        if ($state === ProductVariantModel::STATE_BACKORDER) {
            return 'backorder';
        }

        return 'out_of_stock';
    }

    private function formatGooglePrice(float $amount): string
    {
        if ($amount < 0) {
            $amount = 0;
        }

        $amount = round($amount, 0);

        return number_format($amount, 2, '.', '') . ' HUF';
    }

    private function normalizeDescription(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }

    private function normalizeAttributeName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            $name = mb_strtolower($name, 'UTF-8');
        } else {
            $name = strtolower($name);
        }

        $name = str_replace([' ', '-', '_'], '', $name);

        $map = [
            'gyarto' => 'gyarto',
            'gyártó' => 'gyarto',
            'vonalkod' => 'vonalkod',
            'vonalkód' => 'vonalkod',
            'cikkszam' => 'cikkszam',
            'cikkszám' => 'cikkszam',
            'marka' => 'marka',
            'márka' => 'marka',
        ];

        return $map[$name] ?? $name;
    }

    private function extractAttributeValue(array $attrs, array $keys): string
    {
        foreach ($keys as $key) {
            $normalizedKey = $this->normalizeAttributeName($key);
            if (isset($attrs[$normalizedKey])) {
                return trim((string) $attrs[$normalizedKey]);
            }
        }

        return '';
    }

    private function buildProductType(string $categoryName, string $categoryPath): string
    {
        $categoryName = trim($categoryName);
        if ($categoryName !== '') {
            return $categoryName;
        }

        $categoryPath = trim($categoryPath, '/');
        if ($categoryPath === '') {
            return 'Termekek';
        }

        return str_replace('/', ' > ', $categoryPath);
    }
}
