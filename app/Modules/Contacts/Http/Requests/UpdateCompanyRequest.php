<?php

namespace App\Modules\Contacts\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:200',
                Rule::unique('companies', 'name')
                    ->ignore($this->route('company')?->id)
                    ->whereNull('deleted_at'),
            ],
            'vat' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
