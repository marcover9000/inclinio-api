<?php

namespace App\Modules\Projects\Domain\Billing;

use App\Modules\Projects\Domain\Enums\BillingMode;
use App\Modules\Shared\Domain\ValueObjects\Money;

/**
 * Normalitzador del mode de facturació d'un pack. Extret per DRY:
 * l'utilitzen AddHoursPack i UpdateHoursPack.
 *  - Fixed  → `price` tal qual; `hours` opcional o null; `hourly_rate` null.
 *  - Hourly → `price` = `hours` × `hourly_rate` (es calcula aquí).
 * Compat enrere: sense `billing_mode` → Fixed.
 */
final class PackBilling
{
    /**
     * Normalitza el mode de facturació d'un pack a partir de l'array d'entrada.
     *
     * @param  array{
     *   billing_mode?:BillingMode|string,
     *   hours?:?int,
     *   price?:Money,
     *   hourly_rate?:Money,
     * }  $pack
     * @return array{billing_mode:BillingMode, hours:?int, hourly_rate:?Money, price:Money}
     */
    public static function normalize(array $pack): array
    {
        $mode = $pack['billing_mode'] ?? BillingMode::Fixed;
        if (! $mode instanceof BillingMode) {
            $mode = BillingMode::from($mode);
        }

        if ($mode === BillingMode::Hourly) {
            $hours = (int) $pack['hours'];
            $rate = $pack['hourly_rate'];

            return [
                'billing_mode' => BillingMode::Hourly,
                'hours' => $hours,
                'hourly_rate' => $rate,
                'price' => Money::fromCents($hours * $rate->amountCents, $rate->currency),
            ];
        }

        return [
            'billing_mode' => BillingMode::Fixed,
            'hours' => $pack['hours'] ?? null,
            'hourly_rate' => null,
            'price' => $pack['price'],
        ];
    }
}
