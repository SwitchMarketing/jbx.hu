<?php

namespace App\Libraries;

use App\Models\SettingModel;

class ShopSettings
{
    public const KEY_VAT_RATE_PERCENT = 'vat_rate_percent';
    public const DEFAULT_VAT_RATE_PERCENT = 27.0;

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
}
