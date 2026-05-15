<?php

namespace App\Modules\Contacts\Http\Resources;

// NOTE: cross-module import. The Contacts module references a Crm resource
// here to expose the Company's leads when the relation is loaded. Same
// one-way Contacts ← Crm coupling we use elsewhere (CreateLead in Crm
// depends on Contacts Actions). Avoid the reverse direction.
use App\Modules\Crm\Http\Resources\LeadResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vat' => $this->vat,
            'website' => $this->website,
            'address' => $this->address,
            'notes' => $this->notes,
            'is_client' => $this->is_client,
            'became_client_at' => $this->became_client_at,
            'people' => PersonResource::collection($this->whenLoaded('people')),
            'leads' => LeadResource::collection($this->whenLoaded('leads')),
            'created_at' => $this->created_at,
        ];
    }
}
