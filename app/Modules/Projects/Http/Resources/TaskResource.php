<?php

namespace App\Modules\Projects\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'estimate_hours' => $this->estimate_hours !== null ? (float) $this->estimate_hours : null,
            'consumed_hours' => (int) $this->timeEntries->sum('minutes') / 60,
            'created_at' => $this->created_at,
        ];
    }
}
