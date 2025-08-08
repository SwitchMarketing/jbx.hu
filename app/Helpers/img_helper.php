<?php 

function img_src($filename) {
    return 'imgs/'.$filename;
}

function placeholder($size = null) {
    return 'https://placehold.co/' . $size;
}

function product_image($filename) {
    return 'imgs/products/'.$filename;
}

function product_cover_image($product, $size = null) {

    if (empty($product->images) || !is_array($product->images)) {        
        return placeholder($size);
    }

    $cover_image = reset($product->images)->filename;
    return product_image($cover_image);
}