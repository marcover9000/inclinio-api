<?php

namespace App\Modules\Projects\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'task_id' => $this->task_id,
            'worked_on' => $this->worked_on?->toDateString(),
            'minutes' => $this->minutes,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
