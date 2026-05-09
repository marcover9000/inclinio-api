<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/*
 * Transformació JSON del User per exposar a la SPA.
 * Limita els camps a id, name, email, role.
 */
class UserResource extends JsonResource
{
    /** @var User */
    public $resource;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'role' => $this->resource->roles->first()?->name,
        ];
    }
}
