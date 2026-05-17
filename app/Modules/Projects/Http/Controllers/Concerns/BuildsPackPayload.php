<?php

namespace App\Modules\Projects\Http\Controllers\Concerns;

use App\Modules\Shared\Domain\ValueObjects\Money;

/**
 * Converteix l'input HTTP validat d'un pack a la forma que esperen les
 * accions de domini. Compat enrere: sense `billing_mode` ⇒ 'fixed'.
 *  - fixed  → price (Money) + hours opcional (o null)
 *  - hourly → hours + hourly_rate (Money); el preu el calcula l'acció
 */
trait BuildsPackPayload
{
    /**
     * @param  array<string,mixed>  $in
     * @return array<string,mixed>
     */
    protected function packFromInput(array $in): array
    {
        $mode = $in['billing_mode'] ?? 'fixed';
        $currency = $in['currency'];

        $pack = [
            'billing_mode' => $mode,
            'reason' => $in['reason'],
            'dated_on' => $in['dated_on'] ?? null,
        ];

        if (($in['source_lead_id'] ?? null) !== null) {
            $pack['source_lead_id'] = (int) $in['source_lead_id'];
        }

        if ($mode === 'hourly') {
            $pack['hours'] = (int) $in['hours'];
            $pack['hourly_rate'] = Money::fromCents((int) $in['hourly_rate_cents'], $currency);
        } else {
            $pack['price'] = Money::fromCents((int) $in['price_cents'], $currency);
            $pack['hours'] = isset($in['hours']) ? (int) $in['hours'] : null;
        }

        return $pack;
    }
}
