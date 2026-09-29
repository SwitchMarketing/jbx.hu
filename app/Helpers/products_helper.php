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
        $html .= '<div class="pd-price__gross"><span class="pd-price__label">Bruttó ár</span>' . format_price($price->gross) . '</div>';
        $html .= '<div class="pd-price__vat"><span class="pd-price__label">ÁFA</span>+' . number_format($price->vatRatePercent, 0, '', ' ') . '%</div>';
        $html .= '</div>'; // pd-price__box--secondary
        $html .= '</div>'; // pd-price__row
        $html .= '</div>'; // pd-price
        return $html;
    }
    
    if (empty($product->prices)) {
        return $return_price_only ? 0 : '<div class="pd-price pd-price--empty mb-3"><div class="pd-price__empty">Kérjen gyors ajánlatot.</div></div>';
    }

    $result = json_decode($product->prices);    

    if (empty($result) || !is_object($result)) {
        return $return_price_only ? 0 : '<div class="pd-price pd-price--empty mb-3"><div class="pd-price__empty">Kérjen gyors ajánlatot.</div></div>';
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