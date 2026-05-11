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
function format_price(float $price = 0, ?string $currency = null): string
{
    return number_format($price, 0, '', ' ') . ' ' . (!$currency ? 'Ft' : $currency);
}

/**
 * shop_price_breakdown
 *
 * Nettó ár alapján visszaadja a bruttó és áfa értékeket.
 *
 * @param mixed $netAmount
 * @param mixed $vatRatePercent
 * @return object
 */
function shop_price_breakdown($netAmount = 0, $vatRatePercent = null)
{
    $netAmount = is_numeric($netAmount) ? (float) $netAmount : 0.0;
    if ($netAmount < 0) {
        $netAmount = 0.0;
    }

    if ($vatRatePercent === null || !is_numeric($vatRatePercent) || (float) $vatRatePercent < 0) {
        $vatRatePercent = \App\Libraries\ShopSettings::vatRatePercent();
    } else {
        $vatRatePercent = (float) $vatRatePercent;
    }

    $netAmount = round($netAmount, 2);
    $vatAmount = round($netAmount * ($vatRatePercent / 100), 2);
    $grossAmount = round($netAmount + $vatAmount, 2);

    return (object) [
        'net' => $netAmount,
        'vat' => $vatAmount,
        'gross' => $grossAmount,
        'vatRatePercent' => $vatRatePercent,
        'hasPrice' => $netAmount > 0,
    ];
}

/**
 * shop_net_from_gross
 *
 * Bruttó ár alapján nettó érték számítása.
 *
 * @param mixed $grossAmount
 * @param mixed $vatRatePercent
 * @return float
 */
function shop_net_from_gross($grossAmount = 0, $vatRatePercent = null): float
{
    $grossAmount = is_numeric($grossAmount) ? (float) $grossAmount : 0.0;
    if ($grossAmount <= 0) {
        return 0.0;
    }

    if ($vatRatePercent === null || !is_numeric($vatRatePercent) || (float) $vatRatePercent < 0) {
        $vatRatePercent = \App\Libraries\ShopSettings::vatRatePercent();
    } else {
        $vatRatePercent = (float) $vatRatePercent;
    }

    $multiplier = 1 + ($vatRatePercent / 100);
    if ($multiplier <= 0) {
        return 0.0;
    }

    return round($grossAmount / $multiplier, 2);
}

/**
 * shop_effective_net_price_eur
 *
 * A termék/variáció effektív nettó ára EUR-ban (kedvezmény figyelembevételével).
 *
 * @param mixed $price
 * @param mixed $discountPrice
 * @return float
 */
function shop_effective_net_price_eur($price = 0, $discountPrice = null): float
{
    $base = is_numeric($price) ? (float) $price : 0.0;
    $discount = is_numeric($discountPrice) ? (float) $discountPrice : 0.0;

    if ($base < 0) {
        $base = 0.0;
    }
    if ($discount < 0) {
        $discount = 0.0;
    }

    if ($discount > 0 && ($base <= 0 || $discount < $base)) {
        return $discount;
    }

    return $base;
}

/**
 * shop_eur_to_huf
 *
 * EUR összeget HUF-ra vált a beállított árfolyam alapján.
 *
 * @param mixed $amountEur
 * @return float
 */
function shop_eur_to_huf($amountEur = 0): float
{
    $amount = is_numeric($amountEur) ? (float) $amountEur : 0.0;
    if ($amount <= 0) {
        return 0.0;
    }

    return round($amount * \App\Libraries\ShopSettings::eurToHufRate(), 2);
}

/**
 * shop_price_breakdown_huf_from_eur_net
 *
 * EUR nettó árból HUF nettó/bruttó/áfa bontás.
 *
 * @param mixed $netAmountEur
 * @param mixed $vatRatePercent
 * @return object
 */
function shop_price_breakdown_huf_from_eur_net($netAmountEur = 0, $vatRatePercent = null)
{
    $netHuf = shop_eur_to_huf($netAmountEur);
    return shop_price_breakdown($netHuf, $vatRatePercent);
}
