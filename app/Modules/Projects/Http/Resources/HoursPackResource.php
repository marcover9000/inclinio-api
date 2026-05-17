<?php

namespace App\Modules\Projects\Http\Resources;

use App\Modules\Shared\Http\Resources\Concerns\SerializesMoney;
use Illuminate\Http\Resources\Json\JsonResource;

class HoursPackResource extends JsonResource
{
    use SerializesMoney;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'hours' => $this->hours,
            'billing_mode' => $this->billing_mode->value,
            'price' => $this->money($this->price),
            'hourly_rate' => $this->money($this->hourly_rate),
            'dated_on' => $this->dated_on?->toDateString(),
            'reason' => $this->reason,
            'source_lead_id' => $this->source_lead_id,
            'created_at' => $this->created_at,
        ];
    }
}
