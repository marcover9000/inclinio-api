<?php

namespace App\Modules\Crm\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;

class UpdateLeadRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'message' => ['sometimes', 'string', 'min:5', 'max:5000'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
