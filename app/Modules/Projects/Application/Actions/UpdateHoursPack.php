<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Billing\PackBilling;
use App\Modules\Projects\Domain\Models\HoursPack;

/**
 * Actualitza un pack d'hores existent (edició de billing, raó i data).
 * IMPORTANT: NO reobre el projecte (el reobre és regla exclusiva d'AddHoursPack,
 * spec §5b). NO toca `project_id` ni `source_lead_id` (immutables post-creació).
 * `dated_on`: si ve a $data s'usa; si no, es conserva el valor existent del pack.
 *
 * @param  array{
 *   billing_mode?:BillingMode|string,
 *   hours?:?int,
 *   price?:Money,
 *   hourly_rate?:Money,
 *   reason:string,
 *   dated_on?:?string,
 * }  $data
 */
class UpdateHoursPack
{
    public function __invoke(HoursPack $pack, array $data): HoursPack
    {
        $billing = PackBilling::normalize($data);

        $pack->update([
            ...$billing,
            'reason' => $data['reason'],
            'dated_on' => $data['dated_on'] ?? $pack->dated_on,
        ]);

        return $pack->fresh();
    }
}
