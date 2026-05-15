<?php

namespace App\Modules\Contacts\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class CreateCompanyRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200',
                Rule::unique('companies', 'name')->whereNull('deleted_at'),
            ],
            'vat' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
