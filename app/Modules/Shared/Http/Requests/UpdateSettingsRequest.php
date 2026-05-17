<?php

namespace App\Modules\Shared\Http\Requests;

class UpdateSettingsRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'shadow_rate_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }
}
