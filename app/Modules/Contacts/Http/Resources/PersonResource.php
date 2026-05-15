<?php

namespace App\Modules\Contacts\Http\Resources;

// NOTE: cross-module import. The Contacts module references a Crm resource
// here to expose the Person's leads when the relation is loaded. Same
// one-way Contacts ← Crm coupling we use elsewhere (CreateLead in Crm
// depends on Contacts Actions). Avoid the reverse direction.
use App\Modules\Crm\Http\Resources\LeadResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'is_client' => $this->is_client,
            'became_client_at' => $this->became_client_at,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'leads' => LeadResource::collection($this->whenLoaded('leads')),
            'created_at' => $this->created_at,
        ];
    }
}
