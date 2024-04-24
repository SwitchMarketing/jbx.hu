<?php

/**
 * active_url_class
 * 
 * @param  mixed $segment
 * @return void
 */
function active_url_class($segment = null) {
    if(!$segment)
        return (uri_string() == '') ? 'active' : '';
    else
        return stristr(uri_string(), $segment) ? 'active' : '';
}

/**
 * page_title
 *
 * az oldal címe
 * 
 * @param  mixed $title
 * @return void
 */
function page_title($title = '')
{
    $title = $title . ' | ' . config('Config\\AppConfig')->defaultTitle;
    return trim($title, ' | ');
}

/**
 * default_phone_number
 *
 * @param  mixed $strip_spaces
 * @return void
 */
function default_phone_number($strip_spaces = false)
{
    $phone = config( 'Config\\AppConfig' )->sitePhone;
    return ($strip_spaces === true) ? str_replace([' ','-'], '',$phone) : $phone;
}


/**
 * format_price
 *
 * @param  mixed $price
 * @param  mixed $currency
 * @return string
 */
function format_price(int $price = 0, string $currency = null): string
{
    return number_format($price, 0, '', ' ') . ' ' . (!$currency ? 'Ft' : $currency);
}