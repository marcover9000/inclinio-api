<?php

namespace App\Modules\Contacts\Http\Resources;

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
            'created_at' => $this->created_at,
        ];
    }
}
