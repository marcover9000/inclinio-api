<?php

namespace App\Modules\Contacts\Http\Resources;

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
            'created_at' => $this->created_at,
        ];
    }
}
