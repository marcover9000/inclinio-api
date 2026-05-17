<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Enums\BillingMode;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

/**
 * Afegeix una venda/pack a un projecte (i el reobre si estava acabat/arxivat).
 * Punt ÚNIC on es normalitza la facturació del pack:
 *  - Fixed  → `price` tal qual; `hours` opcional (estimació) o null.
 *  - Hourly → `price` = `hours` × `hourly_rate` (es calcula i es guarda).
 * Compat enrere: si no ve `billing_mode`, s'assumeix Fixed amb el `price` donat.
 *
 * @param array{
 *   billing_mode?:BillingMode|string,
 *   hours?:?int,
 *   price?:Money,
 *   hourly_rate?:Money,
 *   reason:string,
 *   dated_on?:?string,
 *   source_lead_id?:?int
 * } $pack
 */
class AddHoursPack
{
    public function __invoke(Project $project, array $pack): HoursPack
    {
        $mode = $pack['billing_mode'] ?? BillingMode::Fixed;
        if (! $mode instanceof BillingMode) {
            $mode = BillingMode::from($mode);
        }

        if ($mode === BillingMode::Hourly) {
            $hours = (int) $pack['hours'];
            $rate = $pack['hourly_rate'];
            $billing = [
                'billing_mode' => BillingMode::Hourly,
                'hours' => $hours,
                'hourly_rate' => $rate,
                'price' => Money::fromCents($hours * $rate->amountCents, $rate->currency),
            ];
        } else {
            $billing = [
                'billing_mode' => BillingMode::Fixed,
                'hours' => $pack['hours'] ?? null,
                'hourly_rate' => null,
                'price' => $pack['price'],
            ];
        }

        return DB::transaction(function () use ($project, $pack, $billing) {
            // Una ampliació sobre un projecte tancat el reobre (spec §5b/§6).
            if (in_array($project->status, [ProjectStatus::Done, ProjectStatus::Archived], true)) {
                $project->update(['status' => ProjectStatus::Active]);
            }

            return $project->hoursPacks()->create([
                ...$billing,
                'reason' => $pack['reason'],
                'dated_on' => $pack['dated_on'] ?? now()->toDateString(),
                'source_lead_id' => $pack['source_lead_id'] ?? null,
            ]);
        });
    }
}
