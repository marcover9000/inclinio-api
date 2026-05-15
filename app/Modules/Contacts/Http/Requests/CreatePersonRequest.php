<?php

namespace App\Modules\Contacts\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;

class CreatePersonRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:32'],
            'position' => ['nullable', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ];
    }
}
