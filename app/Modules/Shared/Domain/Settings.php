<?php

namespace App\Modules\Shared\Domain;

use App\Modules\Shared\Domain\Models\Setting;
use App\Modules\Shared\Domain\ValueObjects\Money;

/**
 * Accessor tipat de la configuració global. Per ara només la tarifa-ombra
 * (cost/hora). Clau-valor a la taula `settings`. Sense framework genèric (YAGNI).
 */
final class Settings
{
    public static function shadowRate(): Money
    {
        $cents = (int) self::get('shadow_rate_cents', '3000');
        $currency = self::get('shadow_rate_currency', 'EUR');

        return Money::fromCents($cents, $currency);
    }

    public static function setShadowRate(Money $rate): void
    {
        self::put('shadow_rate_cents', (string) $rate->amountCents);
        self::put('shadow_rate_currency', $rate->currency);
    }

    private static function get(string $key, string $default): string
    {
        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }

    private static function put(string $key, string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
