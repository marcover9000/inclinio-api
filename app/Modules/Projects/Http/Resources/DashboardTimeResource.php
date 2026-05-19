<?php

namespace App\Modules\Projects\Http\Resources;

use App\Modules\Shared\Http\Resources\Concerns\SerializesMoney;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serialitza el resultat de TimeDashboard. Money via el mateix trait
 * SerializesMoney que ProjectResource (shape {cents,currency,formatted}).
 *
 * JSON_PRESERVE_ZERO_FRACTION assegura que els floats enters (2.0, 1.0…)
 * es serialitzen com a 2.0 i no com a 2 (int), necessari per als tests.
 */
class DashboardTimeResource extends JsonResource
{
    use SerializesMoney;

    public function jsonOptions(): int
    {
        return JSON_PRESERVE_ZERO_FRACTION;
    }

    public function toArray($request): array
    {
        /** @var array $r */
        $r = $this->resource;

        return [
            'kpis' => [
                'hours_today' => $r['kpis']['hours_today'],
                'hours_week' => $r['kpis']['hours_week'],
                'hours_month' => $r['kpis']['hours_month'],
                'theoretical_cost' => $this->money($r['kpis']['theoretical_cost']),
                'real_margin' => $this->money($r['kpis']['real_margin']),
            ],
            'hours_by_project' => $r['hours_by_project'],
            'billable_split' => $r['billable_split'],
            'range' => $r['range'],
        ];
    }
}
