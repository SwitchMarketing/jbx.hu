<?php

/**
 * products
 *
 * termék opciók az űrlapon
 * 
 * @param  int $selected
 * @return array
 */
function product_options(int $selected = 0) : array {
    return [
        (object) [
            'id' => 1,
            'value' => 'X-Guard',
            'label' => 'X-Guard | Gépbiztonsági kerítés',
            'selected' => ($selected == 1)
        ],
        (object) [
            'id' => 6,
            'value' => 'NTF Protect',
            'label' => 'NTF Protect | Rozsdamentes gépvédő kerítés',
            'selected' => ($selected == 6)
        ],
        (object) [
            'id' => 2,
            'value' => 'Wire Tray',
            'label' => 'Wire Tray | Rácsos kábeltálca rendszerek',
            'selected' => ($selected == 2)
        ],
        (object) [
            'id' => 3,
            'value' => 'X-Protect',
            'label' => 'X-Protect | Ütközésvédelem',
            'selected' => ($selected == 3)
        ],
        (object) [
            'id' => 4,
            'value' => 'X-Store',
            'label' => 'X-Store | Raktárbiztonság',
            'selected' => ($selected == 4)
        ],
        /*
        (object) [
            'id' => 5,
            'value' => 'Ingatlan',
            'label' => 'Ingatlan megoldások',
            'selected' => ($selected == 5)
        ]
        */
    ];
}


/**
 * product_description
 *
 * convert text new lines to HTML paragraphs
 * 
 * @param  mixed $text
 * @return void
 */
function product_description($text = null)
{
    if (empty($text)) {
        return '';
    }    
    $text = trim($text);
    $text = str_replace("<br />", "\n", $text);
    $text = explode("\n", $text);
    $text = array_map(function($line) {
        if(empty($line)) {
            return '';
        }
        return '<p>' . trim($line) . '</p>';
    }, $text);
    $text = array_filter($text, function($line) {
        return !empty(trim($line)) && $line !== '<p></p>';
    });
    $text = implode("\n", $text);
    return $text;
    
}


/**
 * product_price_stock_block
 *
 * Az ársáv "Készlet" blokkja (a bruttó ár / ÁFA alatt). Üres, ha nincs mit kiírni.
 *
 * @param  object $product
 * @return string
 */
function product_price_stock_block($product) {
    $stock = product_stock_label($product);
    if ($stock === '') {
        return '';
    }

    return '<div class="pd-price__stock"><span class="pd-price__label">Készlet</span>' . $stock . '</div>';
}


/**
 * product_google_prices
 *
 * Bruttó HUF ár (és akciós ár) a nettó EUR árból, ahogy a Google Merchant
 * feed és a termékoldal strukturált adata várja: price = eredeti bruttó ár,
 * sale_price = akciós bruttó ár (vagy null).
 *
 * @param  float $priceEurNet
 * @param  mixed $discountPriceEurNet
 * @return array{price: float, sale_price: float|null}
 */
function product_google_prices($priceEurNet, $discountPriceEurNet = null) {
    $vatRate = \App\Libraries\ShopSettings::vatRatePercent();
    $baseNetEur = max(0.0, (float) $priceEurNet);
    $discountNetEur = is_numeric($discountPriceEurNet) ? max(0.0, (float) $discountPriceEurNet) : 0.0;

    $baseGrossHuf = shop_price_breakdown_huf_from_eur_net($baseNetEur, $vatRate)->gross;
    $effectiveNetEur = shop_effective_net_price_eur($baseNetEur, $discountNetEur);
    $effectiveGrossHuf = shop_price_breakdown_huf_from_eur_net($effectiveNetEur, $vatRate)->gross;

    $hasSale = $discountNetEur > 0 && $discountNetEur < $baseNetEur;

    return [
        'price'      => $hasSale ? $baseGrossHuf : $effectiveGrossHuf,
        'sale_price' => $hasSale ? $effectiveGrossHuf : null,
    ];
}


/**
 * product_json_ld
 *
 * schema.org Product/Offer strukturált adat a termékoldalhoz. A Google Merchant
 * Center ebből ellenőrzi a feedben megadott árat és elérhetőséget.
 *
 * @param  object $product   ShopProducts::product() által összeállított objektum
 * @param  string $url       a variáns kanonikus URL-je
 * @return string            <script type="application/ld+json"> blokk, vagy üres string
 */
function product_json_ld($product, $url) {
    if (empty($product) || !is_object($product) || empty($product->price) || (float) $product->price <= 0) {
        return '';
    }

    $prices = product_google_prices((float) $product->price, $product->discount_price ?? null);
    $effectivePrice = $prices['sale_price'] ?? $prices['price'];

    $availabilityMap = [
        'instock'   => 'https://schema.org/InStock',
        'backorder' => 'https://schema.org/BackOrder',
    ];

    $images = [];
    if (!empty($product->images) && is_array($product->images)) {
        foreach (array_slice($product->images, 0, 10) as $image) {
            $images[] = base_url(product_image($image->filename));
        }
    }

    $description = html_entity_decode((string) ($product->description ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $description = trim(preg_replace('/\s+/u', ' ', strip_tags($description)) ?? '');

    $data = [
        '@context' => 'https://schema.org/',
        '@type'    => 'Product',
        'name'     => trim(preg_replace('/\s+/u', ' ', (string) $product->name) ?? ''),
        'sku'      => (string) ($product->sku ?? ''),
        // Az SKU az Axelent gyártói cikkszáma (lásd GoogleMerchantFeed::SKU_IS_MPN)
        'mpn'      => (string) ($product->sku ?? ''),
        'brand'    => ['@type' => 'Brand', 'name' => 'Axelent'],
        'offers'   => [
            '@type'         => 'Offer',
            'url'           => $url,
            'priceCurrency' => 'HUF',
            'price'         => number_format(round($effectivePrice), 0, '.', ''),
            'itemCondition' => 'https://schema.org/NewCondition',
            'availability'  => $availabilityMap[$product->state ?? ''] ?? 'https://schema.org/OutOfStock',
            'seller'        => ['@type' => 'Organization', 'name' => 'JBX Trade Kft.'],
        ],
    ];
    if (!empty($images)) {
        $data['image'] = $images;
    }
    if ($description !== '') {
        $data['description'] = mb_substr($description, 0, 5000, 'UTF-8');
    }

    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);

    return '<script type="application/ld+json">' . $json . '</script>';
}


/**
 * product_price
 *
 * @param  mixed $product
 * @return string
 */
function product_price($product, $return_price_only = false) {

    // Új normalizált modell: közvetlen numerikus ár a variánson
    if (isset($product->price) && is_numeric($product->price) && (float)$product->price > 0) {
        $baseNetEur = (float) $product->price;
        $discountNetEur = isset($product->discount_price) ? (float) $product->discount_price : 0.0;
        $effectiveNetEur = shop_effective_net_price_eur($baseNetEur, $discountNetEur);
        $price = shop_price_breakdown_huf_from_eur_net($effectiveNetEur);

        $regularPrice = null;
        $hasDiscount = $discountNetEur > 0 && $discountNetEur < $baseNetEur;
        if ($hasDiscount) {
            $regularPrice = shop_price_breakdown_huf_from_eur_net($baseNetEur, $price->vatRatePercent);
        }

        if ($return_price_only) {
            return $price->net;
        }

        $html = '<div class="pd-price mb-3">';
        $html .= '<div class="pd-price__row">';
        $html .= '<div class="pd-price__box pd-price__box--main">';
        if ($hasDiscount && $regularPrice !== null) {
            $html .= '<div class="pd-price__old">';
            $html .= '<span class="pd-discount-badge"><span><i>Akció</i></span></span>';
            $html .= '<span class="pd-price__old-wrap"><span class="pd-price__label">Eredeti ár</span><del>' . format_price($regularPrice->net) . '</del></span>';
            $html .= '</div>';
        }
        $html .= '<div class="pd-price__current"><span class="pd-price__label">Nettó ár</span>' . format_price($price->net) . '</div>';
        $html .= '</div>';
        $html .= '<div class="pd-price__box pd-price__box--secondary">';
        $html .= '<div class="pd-price__pair">';
        $html .= '<div class="pd-price__gross"><span class="pd-price__label">Bruttó ár</span>' . format_price($price->gross) . '</div>';
        $html .= '<div class="pd-price__vat"><span class="pd-price__label">ÁFA</span>+' . number_format($price->vatRatePercent, 0, '', ' ') . '%</div>';
        $html .= '</div>'; // pd-price__pair
        $html .= product_price_stock_block($product);
        $html .= '</div>'; // pd-price__box--secondary
        $html .= '</div>'; // pd-price__row
        $html .= '</div>'; // pd-price
        return $html;
    }
    
    if (empty($product->prices)) {
        return $return_price_only ? 0 : '<div class="pd-price pd-price--empty mb-3"><div class="pd-price__empty">Kérjen gyors ajánlatot.</div>' . product_price_stock_block($product) . '</div>';
    }

    $result = json_decode($product->prices);

    if (empty($result) || !is_object($result)) {
        return $return_price_only ? 0 : '<div class="pd-price pd-price--empty mb-3"><div class="pd-price__empty">Kérjen gyors ajánlatot.</div>' . product_price_stock_block($product) . '</div>';
    }

    $prices = [];
    
    // az árak tömbben vannak, de lehetnek objektumok is
    // a Type lehet: sale, normal, etc.
    // a Gross az ár
    // ha nincs ár, akkor nem jelenik meg semmi
    if(isset($result->Price)) {

        if (is_object($result->Price) && isset($result->Price->Gross) && is_numeric($result->Price->Gross) && $result->Price->Gross > 0) {
            $prices[$result->Price->Type] = $result->Price->Gross;
        }
        
        if (is_array($result->Price) && !empty($result->Price)) {
            foreach ($result->Price as $price) {
                if (isset($price->Gross) && is_numeric($price->Gross) && $price->Gross > 0) {
                    $prices[$price->Type] = $price->Gross;
                }
            }
        }
    }

    if (empty($prices)) {
        return $return_price_only ? 0 : '<div class="pd-price pd-price--empty mb-3"><div class="pd-price__empty">Kérjen gyors ajánlatot.</div></div>';
    }

    $html = '<ul class="pd-price mb-3">';

    // akciós ár
    if(isset($prices['sale'])) {
        $html .= '<li class="pd-sale-price"><span>' . format_price($prices['sale']) . '</span></li>';
    }

    // nem akciós ár
    if(isset($prices['normal'])) {
        $class = isset($prices['sale']) ? 'pd-regular-price' : 'pd-sale-price';
        $html .= '<li class="' . $class . '"><span>' . format_price($prices['normal']) . '</span></li>';
    }    
    
    $html .= '</ul>';

    if($return_price_only) {
        // csak az ár számértéke kell
        if(isset($prices['sale'])) {
            return $prices['sale'];
        }
        if(isset($prices['normal'])) {
            return $prices['normal'];
        }
        return 0;
    }

    return $html;
    
}


/**
 * add_to_cart_button
 *
 * @param  mixed $product
 * @param  mixed $class
 * @return string
 */
function add_to_cart_button($product, $class = 'theme-btn') {

    // ha nincs terméke akkor nem jelenítünk meg gombot
    if (empty($product) || !is_object($product)) {
        return '';
    }

    $btnText = product_status($product)->btnText;
    $variantAttr = '';
    if (isset($product->variant_id) && (int)$product->variant_id > 0) {
        $variantAttr = ' data-variant-id="' . (int)$product->variant_id . '"';
    }

    return '<a href="javascript:void(0)" data-sku="'.$product->sku.'"' . $variantAttr . ' onclick="App.addToCart(this)" class="theme-btn">' . $btnText . ' <i class="fa-solid fa-angles-right"></i></a>';
}   


/**
 * product_stock
 *
 * a termék raktárkészlete
 * 
 * @param  mixed $product
 * @return void
 */
function product_stock($product) {

    // Új normalizált modell: közvetlen készlet mező
    if (isset($product->stock) && is_numeric($product->stock)) {
        return (float)$product->stock;
    }

    if (empty($product->stock)) {
        return 0;
    }

    $result = json_decode($product->stock);    

    if (empty($result) || !is_object($result)) {
        return 0;
    }

    if(isset($result->Stock)) {
        return (int)$result->Stock->Qty ?? 0;
    }

    return 0;
}   


/**
 * product_stock_label
 *
 * Készletjelző a termék adatlapra: a variáns állapota (state) adja a típust,
 * a raktárkészlet (stock) csak sávosan jelenik meg (nem mutatunk pontos számot).
 *
 * @param  object $product
 * @return string HTML (üres, ha nincs mit kiírni)
 */
function product_stock_label($product) {
    if (empty($product) || !is_object($product)) {
        return '';
    }

    $lowStockLimit = 10;
    $state = (string) ($product->state ?? '');
    $stock = product_stock($product);

    switch ($state) {
        case 'instock':
            $class = 'is-instock';
            $label = 'Raktáron';
            $note  = '';
            if ($stock > 0 && $stock <= $lowStockLimit) {
                $class = 'is-low';
                $note  = 'Utolsó ' . (int) ceil($stock) . ' db';
            }
            break;
        case 'backorder':
            $class = 'is-backorder';
            $label = 'Rendelésre';
            $note  = 'Beszerzés alatt';
            break;
        case 'inquire':
            $class = 'is-inquire';
            $label = 'Ajánlatkérés alapján';
            $note  = '';
            break;
        default:
            return '';
    }

    $html  = '<div class="pd-stock ' . $class . '">';
    $html .= '<span class="pd-stock__dot" aria-hidden="true"></span>';
    $html .= '<span class="pd-stock__label">' . esc($label) . '</span>';
    if ($note !== '') {
        $html .= '<span class="pd-stock__note">' . esc($note) . '</span>';
    }
    $html .= '</div>';

    return $html;
}


/**
 * product_status
 *
 * @param  mixed $product
 * @return object
 */
function product_status($product) {
    $price = (float) product_price($product, true);
    $stock = product_stock($product);

    $status = (object) [
        'btnText' => 'Ajánlatkérés',
        'inStock' => false
    ];

    if ($price > 0) {
        $status->btnText = 'Kosárba';
        if ($stock > 0 || (isset($product->state) && $product->state === 'instock')) {
            $status->inStock = true;
        }
    }    

    return $status;   
}