<?php

namespace App\Controllers;

use App\Models\ProductMasterModel;
use App\Models\ProductVariantModel;
use Config\AppConfig;

/**
 * Google Merchant Center termékfeed (RSS 2.0, g: névtér).
 *
 * Specifikáció: https://support.google.com/merchants/answer/7052112
 */
class GoogleMerchantFeed extends BaseController
{
    /**
     * Ha az SKU-k a gyártó (Axelent) saját cikkszámai, kapcsold true-ra:
     * ekkor az SKU g:mpn-ként is kimegy, és nem küldünk identifier_exists=no-t.
     */
    private const SKU_IS_MPN = true;

    // A product_json_ld() helper is ezt a márkát használja – ha változik, ott is módosítsd
    private const DEFAULT_BRAND = 'Axelent';

    /**
     * Felső szintű kategória slug => Google termékkategória azonosító.
     * Taxonómia: https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt
     */
    private const GOOGLE_CATEGORY_MAP = [
        'gepbiztonsagi-kerites' => 8274,   // Business & Industrial > Industrial Storage > Wire Partitions, Enclosures & Doors
        'raktarbiztonsag'       => 8274,
        'kabeltalca-rendszer'   => 6402,   // Electronics > Electronics Accessories > Cable Management > Cable Trays
        'uetkoezesvedelem'      => 111,    // Business & Industrial
    ];
    private const GOOGLE_CATEGORY_DEFAULT = 111;

    /** Méretet leíró attribútumok (normalizált név), a g:size összeállításához. */
    private const SIZE_ATTRIBUTES = [
        'magassagxszelesseg', 'meret', 'magassag', 'szelesseg', 'hosszusag',
        'maxmagassag', 'maxszelesseg', 'maxnyilasszelesseg', 'minmagassag', 'minszelesseg', 'ccmm',
    ];

    /** Azonosító jellegű attribútumok: nem kerülnek a címbe / product_detail-be. */
    private const IDENTIFIER_ATTRIBUTES = [
        'brand', 'marka', 'gyarto', 'manufacturer',
        'gtin', 'ean', 'vonalkod', 'barcode', 'isbn',
        'mpn', 'manufacturerpartnumber', 'cikkszam', 'partnumber',
    ];

    private const COLOR_TRANSLATIONS = [
        'graphite black' => 'Grafitfekete',
    ];

    private const TITLE_MAX = 150;
    private const DESCRIPTION_MAX = 5000;
    private const DESCRIPTION_MIN = 50;
    private const ADDITIONAL_IMAGES_MAX = 10;
    private const PRODUCT_DETAIL_MAX = 100;

    public function index()
    {
        $appConfig = new AppConfig();
        $rows = $this->getFeedRows();
        $attributeMap = $this->getVariantAttributeMap(array_map(static fn ($row) => (int) $row->variant_id, $rows));
        $imageMap = $this->getVariantImageMap($rows);
        $categories = $this->getCategoryMap();

        // Kiszűrjük a feedbe nem küldhető tételeket (GMC elutasítaná őket)
        $skipped = ['nincs_ar' => 0, 'nincs_slug' => 0, 'nincs_kep' => 0];
        $items = [];
        foreach ($rows as $row) {
            $priceData = product_google_prices((float) ($row->price ?? 0), $row->discount_price ?? null);
            if ($priceData['price'] <= 0) {
                $skipped['nincs_ar']++;
                continue;
            }

            if (trim((string) $row->master_slug) === '' || trim((string) $row->variant_slug) === '') {
                $skipped['nincs_slug']++;
                continue;
            }

            $images = $imageMap[(int) $row->variant_id] ?? [];
            if (empty($images)) {
                $skipped['nincs_kep']++;
                continue;
            }

            $items[] = [
                'row'    => $row,
                'price'  => $priceData,
                'attrs'  => $attributeMap[(int) $row->variant_id] ?? [],
                'images' => $images,
            ];
        }

        $groups = $this->buildGroups($items);

        $itemData = [];
        foreach ($items as $item) {
            $group = $groups[(int) $item['row']->master_id];
            $itemData[] = $this->buildItemData($item, $group, $categories);
        }
        $this->disambiguateTitles($itemData);

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');

        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $xml->startElement('channel');
        $xml->writeElement('title', $appConfig->siteName . ' - Google Merchant feed');
        $xml->writeElement('link', base_url('/'));
        $xml->writeElement('description', 'Google Merchant termékfeed a jbx.hu webshop aktív termékeihez.');
        $xml->writeComment(sprintf(
            ' tételek: %d; kihagyva - nincs ár: %d, nincs slug: %d, nincs kép: %d ',
            count($itemData),
            $skipped['nincs_ar'],
            $skipped['nincs_slug'],
            $skipped['nincs_kep']
        ));

        foreach ($itemData as $data) {
            $this->writeItem($xml, $data);
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setBody($xml->outputMemory());
    }

    private function writeItem(\XMLWriter $xml, array $data): void
    {
        $xml->startElement('item');
        $xml->writeElement('g:id', $data['id']);
        $xml->writeElement('title', $data['title']);
        $xml->writeElement('description', $data['description']);
        $xml->writeElement('link', $data['link']);
        $xml->writeElement('g:image_link', $data['image_link']);
        foreach ($data['additional_image_links'] as $url) {
            $xml->writeElement('g:additional_image_link', $url);
        }
        $xml->writeElement('g:availability', $data['availability']);
        $xml->writeElement('g:condition', 'new');
        $xml->writeElement('g:price', $data['price']);
        if ($data['sale_price'] !== null) {
            $xml->writeElement('g:sale_price', $data['sale_price']);
        }

        $xml->writeElement('g:brand', $data['brand']);
        if ($data['gtin'] !== '') {
            $xml->writeElement('g:gtin', $data['gtin']);
        }
        if ($data['mpn'] !== '') {
            $xml->writeElement('g:mpn', $data['mpn']);
        }
        if ($data['gtin'] === '' && $data['mpn'] === '') {
            $xml->writeElement('g:identifier_exists', 'no');
        }

        $xml->writeElement('g:google_product_category', (string) $data['google_product_category']);
        $xml->writeElement('g:product_type', $data['product_type']);

        if ($data['item_group_id'] !== null) {
            $xml->writeElement('g:item_group_id', $data['item_group_id']);
            $xml->writeElement('g:item_group_title', $data['item_group_title']);
        }
        if ($data['color'] !== '') {
            $xml->writeElement('g:color', $data['color']);
        }
        if ($data['size'] !== '') {
            $xml->writeElement('g:size', $data['size']);
        }
        if ($data['material'] !== '') {
            $xml->writeElement('g:material', $data['material']);
        }

        foreach ($data['product_details'] as $detail) {
            $xml->startElement('g:product_detail');
            $xml->writeElement('g:section_name', 'Műszaki adatok');
            $xml->writeElement('g:attribute_name', $detail['name']);
            $xml->writeElement('g:attribute_value', $detail['value']);
            $xml->endElement();
        }

        $xml->endElement();
    }

    private function getFeedRows(): array
    {
        $db = \Config\Database::connect('shop');

        // Az "inquire" (ajánlatkérés) tételek nem vásárolhatók, ezért nem kerülnek a feedbe
        return $db->table('product_variants pv')
            ->select([
                'pv.id AS variant_id',
                'pv.unas_id AS variant_unas_id',
                'pv.sku AS sku',
                'pv.name AS variant_name',
                'pv.slug AS variant_slug',
                'pv.price AS price',
                'pv.discount_price AS discount_price',
                'pv.state AS variant_state',
                'pm.id AS master_id',
                'pm.name AS master_name',
                'pm.slug AS master_slug',
                'pm.description AS description',
                'pm.category_id AS category_id',
            ])
            ->join('product_masters pm', 'pm.id = pv.master_id', 'inner')
            ->where('pm.state', ProductMasterModel::STATE_ACTIVE)
            ->whereIn('pv.state', [
                ProductVariantModel::STATE_INSTOCK,
                ProductVariantModel::STATE_BACKORDER,
            ])
            ->orderBy('pm.id', 'ASC')
            ->orderBy('pv.position', 'ASC')
            ->orderBy('pv.id', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * variant_id => [ ['name' => eredeti név, 'key' => normalizált név, 'value' => érték], ... ]
     */
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
            ->orderBy('a.position', 'ASC')
            ->orderBy('a.id', 'ASC')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $variantId = (int) ($row->variant_id ?? 0);
            $name = $this->cleanText((string) ($row->attribute_name ?? ''));
            $value = $this->cleanText((string) ($row->value ?? ''));
            $key = $this->normalizeAttributeName($name);
            if ($variantId < 1 || $key === '' || $value === '') {
                continue;
            }

            $map[$variantId][$key] = ['name' => $name, 'key' => $key, 'value' => $value];
        }

        return $map;
    }

    /**
     * variant_id => [abszolút kép URL, ...] a termékoldallal azonos sorrendben
     * (ShopProducts::getVariantImages logikája). Csak a ténylegesen létező fájlok.
     */
    private function getVariantImageMap(array $rows): array
    {
        $masterIds = [];
        $unasIds = [];
        foreach ($rows as $row) {
            $masterIds[(int) $row->master_id] = true;
            if (!empty($row->variant_unas_id)) {
                $unasIds[(string) $row->variant_unas_id] = true;
            }
        }
        if (empty($masterIds)) {
            return [];
        }

        $db = \Config\Database::connect('shop');
        $builder = $db->table('images')
            ->select('id, master_id, variant_id, product_id, filename, position')
            ->groupStart()
                ->whereIn('master_id', array_keys($masterIds));
        if (!empty($unasIds)) {
            $builder->orGroupStart()
                ->where('master_id', null)
                ->whereIn('product_id', array_map('strval', array_keys($unasIds)))
            ->groupEnd();
        }
        $images = $builder->groupEnd()
            ->orderBy('position', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResult();

        $byMaster = [];
        $byUnas = [];
        foreach ($images as $image) {
            $filename = trim((string) $image->filename);
            if ($filename === '' || !$this->imageExists($filename)) {
                continue;
            }
            if ($image->master_id !== null) {
                $byMaster[(int) $image->master_id][] = $image;
            } elseif ($image->product_id !== null) {
                $byUnas[(string) $image->product_id][] = $image;
            }
        }

        $map = [];
        foreach ($rows as $row) {
            $variantId = (int) $row->variant_id;
            $candidates = [];
            foreach ($byMaster[(int) $row->master_id] ?? [] as $image) {
                if ($image->variant_id === null || (int) $image->variant_id === $variantId) {
                    $candidates[] = $image;
                }
            }
            foreach ($byUnas[(string) ($row->variant_unas_id ?? '')] ?? [] as $image) {
                $candidates[] = $image;
            }

            usort($candidates, static fn ($a, $b) => [(int) $a->position, (int) $a->id] <=> [(int) $b->position, (int) $b->id]);

            $urls = [];
            foreach ($candidates as $image) {
                $url = $this->buildImageUrl((string) $image->filename);
                $urls[$url] = true;
            }
            $map[$variantId] = array_slice(array_keys($urls), 0, self::ADDITIONAL_IMAGES_MAX + 1);
        }

        return $map;
    }

    /**
     * unas_id => category_tree sor
     */
    private function getCategoryMap(): array
    {
        $db = \Config\Database::connect('shop');
        $rows = $db->table('category_tree')
            ->select('unas_id, name, slug, path, pathIds')
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->unas_id] = $row;
        }

        return $map;
    }

    /**
     * master_id => ['count' => variánsok száma a feedben, 'varying' => [eltérő attribútum kulcsok]]
     */
    private function buildGroups(array $items): array
    {
        $values = [];
        foreach ($items as $item) {
            $masterId = (int) $item['row']->master_id;
            $values[$masterId]['count'] = ($values[$masterId]['count'] ?? 0) + 1;
            $values[$masterId]['attrs'][] = $item['attrs'];
        }

        $groups = [];
        foreach ($values as $masterId => $group) {
            $keys = [];
            foreach ($group['attrs'] as $attrs) {
                foreach ($attrs as $key => $attr) {
                    $keys[$key] = true;
                }
            }

            $varying = [];
            if ($group['count'] > 1) {
                foreach (array_keys($keys) as $key) {
                    if (in_array($key, self::IDENTIFIER_ATTRIBUTES, true)) {
                        continue;
                    }
                    $distinct = [];
                    foreach ($group['attrs'] as $attrs) {
                        $distinct[mb_strtolower($attrs[$key]['value'] ?? '', 'UTF-8')] = true;
                    }
                    if (count($distinct) > 1) {
                        $varying[] = $key;
                    }
                }
            }

            $groups[$masterId] = ['count' => $group['count'], 'varying' => $varying];
        }

        return $groups;
    }

    private function buildItemData(array $item, array $group, array $categories): array
    {
        $row = $item['row'];
        $attrs = $item['attrs'];
        $priceData = $item['price'];

        $baseName = $this->cleanText((string) ($row->variant_name ?: $row->master_name));
        $title = $this->buildTitle($baseName, $attrs, $group['varying']);

        $brand = $this->extractAttributeValue($attrs, ['brand', 'marka', 'gyarto', 'manufacturer']);
        if ($brand === '') {
            $brand = self::DEFAULT_BRAND;
        }

        $sku = trim((string) ($row->sku ?? ''));
        $gtin = $this->extractAttributeValue($attrs, ['gtin', 'ean', 'vonalkod', 'barcode', 'isbn']);
        $mpn = $this->extractAttributeValue($attrs, ['mpn', 'manufacturerpartnumber', 'cikkszam', 'partnumber']);
        if ($mpn === '' && self::SKU_IS_MPN) {
            $mpn = $sku;
        }

        $category = $categories[(int) ($row->category_id ?? 0)] ?? null;
        $images = $item['images'];

        return [
            'id'                      => mb_substr($sku !== '' ? $sku : ('jbx-' . (int) $row->variant_id), 0, 50, 'UTF-8'),
            'master_id'               => (int) $row->master_id,
            'sku'                     => $sku,
            'title'                   => $title,
            'description'             => $this->buildDescription((string) ($row->description ?? ''), $title, $attrs),
            'link'                    => $this->buildProductUrl($row, $category),
            'image_link'              => $images[0],
            'additional_image_links'  => array_slice($images, 1),
            'availability'            => $this->mapAvailability((string) ($row->variant_state ?? '')),
            'price'                   => $this->formatGooglePrice($priceData['price']),
            'sale_price'              => $priceData['sale_price'] !== null ? $this->formatGooglePrice($priceData['sale_price']) : null,
            'brand'                   => mb_substr($brand, 0, 70, 'UTF-8'),
            'gtin'                    => $gtin,
            'mpn'                     => mb_substr($mpn, 0, 70, 'UTF-8'),
            'google_product_category' => $this->mapGoogleCategory($category, $categories),
            'product_type'            => $this->buildProductType($category, $categories),
            'item_group_id'           => $group['count'] > 1 ? (string) (int) $row->master_id : null,
            'item_group_title'        => mb_substr($this->cleanText((string) $row->master_name), 0, self::TITLE_MAX, 'UTF-8'),
            'color'                   => $this->buildColor($attrs['szin']['value'] ?? ''),
            'size'                    => $group['count'] > 1 ? $this->buildSize($attrs, $group['varying']) : '',
            'material'                => mb_substr($attrs['feluletkezeles']['value'] ?? '', 0, 200, 'UTF-8'),
            'product_details'         => $this->buildProductDetails($attrs),
        ];
    }

    /**
     * Cím: variáns neve + a csoporton belül eltérő attribútumok értékei
     * (pl. "X-Guard – Rácsos panel - Classic – 750x900 mm").
     */
    private function buildTitle(string $baseName, array $attrs, array $varying): string
    {
        $haystack = $this->comparable($baseName);
        $parts = [];
        $seen = [];
        foreach ($varying as $key) {
            $value = $attrs[$key]['value'] ?? '';
            $comparableValue = $this->comparable($value);
            if ($value === '' || isset($seen[$comparableValue]) || strpos($haystack, $comparableValue) !== false) {
                continue;
            }
            $seen[$comparableValue] = true;

            // Méreteknél a név is kell, különben nem derül ki, melyik a magasság és melyik a szélesség
            // (a "2400x1000 mm" jellegű értékek önmagukban is egyértelműek)
            $needsLabel = in_array($key, self::SIZE_ATTRIBUTES, true) && !preg_match('/\d\s*x\s*\d/iu', $value);
            $parts[] = $needsLabel
                ? mb_strtolower(trim(preg_replace('/\s*\([^)]*\)/u', '', $attrs[$key]['name']) ?? ''), 'UTF-8') . ' ' . $value
                : $value;
        }

        $title = $baseName;
        if (!empty($parts)) {
            $title .= ' – ' . implode(', ', $parts);
        }

        return $this->truncate($title, self::TITLE_MAX);
    }

    /**
     * Ha két tételnek mégis azonos lenne a címe, a cikkszámmal tesszük egyedivé.
     */
    private function disambiguateTitles(array &$itemData): void
    {
        $counts = array_count_values(array_map(static fn ($d) => $d['title'], $itemData));
        foreach ($itemData as &$data) {
            if ($counts[$data['title']] > 1 && $data['sku'] !== '') {
                $suffix = ' (' . $data['sku'] . ')';
                $data['title'] = $this->truncate($data['title'], self::TITLE_MAX - mb_strlen($suffix, 'UTF-8')) . $suffix;
            }
        }
        unset($data);
    }

    private function buildDescription(string $description, string $title, array $attrs): string
    {
        $description = $this->normalizeDescription($description);

        // Túl rövid leírásnál a termékoldalon is látható műszaki adatokkal egészítjük ki
        if (mb_strlen($description, 'UTF-8') < self::DESCRIPTION_MIN) {
            $parts = [];
            if ($description !== '' && $this->comparable($description) !== $this->comparable($title)) {
                $parts[] = rtrim($description, '.') . '.';
            } else {
                $parts[] = rtrim($title, '.') . '.';
            }

            $specs = [];
            foreach ($this->buildProductDetails($attrs) as $detail) {
                $specs[] = $detail['name'] . ': ' . $detail['value'];
            }
            if (!empty($specs)) {
                $parts[] = 'Műszaki adatok: ' . implode('; ', $specs) . '.';
            }

            $description = implode(' ', $parts);
        }

        return $this->truncate($description, self::DESCRIPTION_MAX);
    }

    private function buildProductDetails(array $attrs): array
    {
        $details = [];
        foreach ($attrs as $key => $attr) {
            if (in_array($key, self::IDENTIFIER_ATTRIBUTES, true)) {
                continue;
            }
            $details[] = [
                'name'  => mb_substr($attr['name'], 0, 140, 'UTF-8'),
                'value' => mb_substr($attr['value'], 0, 1000, 'UTF-8'),
            ];
        }

        return array_slice($details, 0, self::PRODUCT_DETAIL_MAX);
    }

    /**
     * GMC: a szín nem tartalmazhat számot (RAL kód), több szín "/"-rel elválasztva,
     * színenként max. 40, összesen max. 100 karakter.
     */
    private function buildColor(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $colors = [];
        foreach (preg_split('/\s*-\s*/u', $value) as $part) {
            // "RAL 9011 (Graphite Black)" => "Graphite Black"; "Cink sárga (RAL 1018)" => "Cink sárga"
            if (preg_match('/^RAL\s*\d+\s*\(([^)]+)\)$/iu', trim($part), $m)) {
                $part = $m[1];
            }
            $part = preg_replace('/\([^)]*\d[^)]*\)/u', '', $part) ?? $part;
            $part = preg_replace('/\bRAL\s*\d+\b/iu', '', $part) ?? $part;
            $part = $this->cleanText(preg_replace('/\d+/u', '', $part) ?? $part);
            if (mb_strlen($part, 'UTF-8') < 2) {
                continue;
            }
            // Egy feeden belül egy nyelv: az angol színneveket magyarítjuk
            $part = self::COLOR_TRANSLATIONS[mb_strtolower($part, 'UTF-8')] ?? $part;
            $colors[mb_strtolower($part, 'UTF-8')] = mb_substr($part, 0, 40, 'UTF-8');
        }

        return mb_substr(implode('/', array_values($colors)), 0, 100, 'UTF-8');
    }

    private function buildSize(array $attrs, array $varying): string
    {
        $parts = [];
        foreach (self::SIZE_ATTRIBUTES as $key) {
            if (in_array($key, $varying, true) && isset($attrs[$key])) {
                $parts[$this->comparable($attrs[$key]['value'])] = $attrs[$key]['value'];
            }
        }

        return mb_substr(implode(' / ', array_values($parts)), 0, 100, 'UTF-8');
    }

    private function buildProductUrl(object $row, ?object $category): string
    {
        $segments = ['termekek'];

        $categoryPath = trim((string) ($category->path ?? ''), '/');
        if ($categoryPath !== '') {
            foreach (explode('/', $categoryPath) as $segment) {
                $segment = trim((string) $segment);
                if ($segment !== '') {
                    $segments[] = $segment;
                }
            }
        }

        $segments[] = trim((string) $row->master_slug, '/');
        $segments[] = trim((string) $row->variant_slug, '/');

        return base_url(implode('/', $segments));
    }

    private function imageExists(string $filename): bool
    {
        if (preg_match('#^https?://#i', $filename)) {
            return true;
        }

        return is_file(FCPATH . product_image(ltrim($filename, '/')));
    }

    private function buildImageUrl(string $filename): string
    {
        $filename = trim($filename);
        if (preg_match('#^https?://#i', $filename)) {
            return $filename;
        }

        return base_url(product_image(ltrim($filename, '/')));
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

    private function mapGoogleCategory(?object $category, array $categories): int
    {
        $root = $this->getCategoryTrail($category, $categories)[0] ?? null;
        $slug = $root !== null ? (string) $root->slug : '';

        return self::GOOGLE_CATEGORY_MAP[$slug] ?? self::GOOGLE_CATEGORY_DEFAULT;
    }

    /**
     * Teljes kategóriaút, pl. "Gépbiztonsági kerítés > Ajtók > Tolóajtó középső zárral".
     */
    private function buildProductType(?object $category, array $categories): string
    {
        $names = array_map(fn ($cat) => $this->cleanText((string) $cat->name), $this->getCategoryTrail($category, $categories));
        $names = array_values(array_filter($names, static fn ($name) => $name !== ''));
        if (empty($names)) {
            return 'Termékek';
        }

        return mb_substr(implode(' > ', $names), 0, 750, 'UTF-8');
    }

    private function getCategoryTrail(?object $category, array $categories): array
    {
        if ($category === null) {
            return [];
        }

        $trail = [];
        foreach (explode('/', (string) ($category->pathIds ?? '')) as $id) {
            if (isset($categories[(int) $id])) {
                $trail[] = $categories[(int) $id];
            }
        }

        return !empty($trail) ? $trail : [$category];
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
        $text = preg_replace('#<(br|/p|/li|/h\d)\b[^>]*>#i', ' ', $text) ?? $text;
        $text = strip_tags($text);
        return $this->cleanText($text);
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text, " \t\n\r\0\x0B-–");
    }

    /**
     * Összehasonlításhoz: kisbetű, szóközök nélkül.
     */
    private function comparable(string $text): string
    {
        return preg_replace('/\s+/u', '', mb_strtolower($text, 'UTF-8')) ?? $text;
    }

    private function truncate(string $text, int $max): string
    {
        if (mb_strlen($text, 'UTF-8') <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1, 'UTF-8');
        $space = mb_strrpos($cut, ' ', 0, 'UTF-8');
        if ($space !== false && $space > $max * 0.6) {
            $cut = mb_substr($cut, 0, $space, 'UTF-8');
        }

        return rtrim($cut, " ,;–-") . '…';
    }

    private function normalizeAttributeName(string $name): string
    {
        $name = mb_strtolower(trim($name), 'UTF-8');
        $name = strtr($name, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ú' => 'u', 'ü' => 'u', 'ű' => 'u',
        ]);

        return preg_replace('/[^a-z0-9]/', '', $name) ?? '';
    }

    private function extractAttributeValue(array $attrs, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($attrs[$key])) {
                return $attrs[$key]['value'];
            }
        }

        return '';
    }
}
