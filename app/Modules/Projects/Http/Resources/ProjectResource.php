<?php

namespace App\Modules\Projects\Http\Resources;

use App\Modules\Contacts\Http\Resources\CompanyResource;
use App\Modules\Contacts\Http\Resources\PersonResource;
use App\Modules\Shared\Http\Resources\Concerns\SerializesMoney;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    use SerializesMoney;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'is_internal' => $this->is_internal,
            'client_company_id' => $this->client_company_id,
            'client_person_id' => $this->client_person_id,
            'client_company' => new CompanyResource($this->whenLoaded('clientCompany')),
            'client_person' => new PersonResource($this->whenLoaded('clientPerson')),
            'shadow_rate_override' => $this->money($this->shadow_rate_override),
            'total_price' => $this->money($this->totalPrice()),
            'budgeted_hours' => $this->budgetedHours(),
            'started_at' => $this->started_at?->toDateString(),
            'due_at' => $this->due_at?->toDateString(),
            'hours_packs' => $this->whenLoaded('hoursPacks', fn ($packs) => HoursPackResource::collection($packs)->resolve($request)),
            'aggregates' => [
                'consumed_hours' => $this->consumedHours(),
                'overrun_percent' => $this->overrunPercent(),
                'theoretical_cost' => $this->money($this->theoreticalCost()),
                'real_margin' => $this->money($this->realMargin()),
                'shadow_rate' => $this->money($this->shadowRate()),
            ],
            'tasks' => $this->whenLoaded('tasks', fn ($t) => TaskResource::collection($t)->resolve($request)),
            'time_entries' => $this->whenLoaded('timeEntries', fn ($te) => TimeEntryResource::collection($te)->resolve($request)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
