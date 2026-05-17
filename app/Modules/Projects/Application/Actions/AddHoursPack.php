<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Billing\PackBilling;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Afegeix una venda/pack a un projecte i el reobre si estava acabat/arxivat
 * (spec §5b/§6). La normalització de la facturació (fixed/hourly) viu a
 * PackBilling::normalize().
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
        $billing = PackBilling::normalize($pack);

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
