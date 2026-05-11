<?php

namespace App\Libraries;

use App\Models\SettingModel;

class ShopSettings
{
    public const KEY_VAT_RATE_PERCENT = 'vat_rate_percent';
    public const DEFAULT_VAT_RATE_PERCENT = 27.0;
    public const KEY_EUR_TO_HUF_RATE = 'eur_to_huf_rate';
    public const DEFAULT_EUR_TO_HUF_RATE = 400.0;

    public static function vatRatePercent(): float
    {
        $model = new SettingModel();
        $value = $model->getValue(self::KEY_VAT_RATE_PERCENT, self::DEFAULT_VAT_RATE_PERCENT);
        $value = is_numeric($value) ? (float) $value : self::DEFAULT_VAT_RATE_PERCENT;

        if ($value < 0) {
            $value = self::DEFAULT_VAT_RATE_PERCENT;
        }

        return $value;
    }

    public static function vatMultiplier(): float
    {
        return 1 + (self::vatRatePercent() / 100);
    }

    public static function eurToHufRate(): float
    {
        $model = new SettingModel();
        $value = $model->getValue(self::KEY_EUR_TO_HUF_RATE, self::DEFAULT_EUR_TO_HUF_RATE);
        $value = is_numeric($value) ? (float) $value : self::DEFAULT_EUR_TO_HUF_RATE;

        if ($value <= 0) {
            $value = self::DEFAULT_EUR_TO_HUF_RATE;
        }

        return $value;
    }
}
