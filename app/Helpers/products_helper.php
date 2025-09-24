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
    
    if (empty($product->prices)) {
        return '';
    }

    $result = json_decode($product->prices);    

    if (empty($result) || !is_object($result)) {
        return '';
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
        return '';
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

    return '<a href="javascript:void(0)" data-sku="'.$product->sku.'" onclick="App.addToCart(this)" class="theme-btn">' . $btnText . ' <i class="fa-solid fa-angles-right"></i></a>';
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

    $price = product_price($product);
    $stock = product_stock($product);

    $status = (object) [
        'btnText' => '',
        'inStock' => false
    ];

    // ha van ár
    if(!empty($price)) {

        // ha van raktáron
        if($stock > 0) {
            $status->inStock = true;
            $status->btnText = 'Kosárba';            
        } else {
            $status->btnText = 'Rendelés';     
        }
        
    } else {
        $status->btnText = 'Ajánlatkérés';
    }    

    return $status;   
}