<?php

namespace App\Modules\Crm\Http\Resources;

use App\Modules\Contacts\Http\Resources\CompanyResource;
use App\Modules\Contacts\Http\Resources\PersonResource;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'source' => $this->source->value,
            'message' => $this->message,
            'tags' => $this->tags,
            'status_changed_at' => $this->status_changed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'person' => new PersonResource($this->whenLoaded('person')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'notes' => LeadNoteResource::collection($this->whenLoaded('notes')),
        ];
    }
}
