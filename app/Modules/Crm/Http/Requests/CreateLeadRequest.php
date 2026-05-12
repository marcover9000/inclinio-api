<?php

namespace App\Modules\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'person.first_name' => ['required', 'string', 'max:100'],
            'person.last_name' => ['nullable', 'string', 'max:100'],
            'person.email' => ['nullable', 'email', 'max:150'],
            'person.phone' => ['nullable', 'string', 'max:32'],
            'person.position' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'array'],
            'company.name' => ['required_with:company', 'string', 'max:200'],
            'company.vat' => ['nullable', 'string', 'max:32'],
            'company.website' => ['nullable', 'url', 'max:255'],
            'company.address' => ['nullable', 'string'],
            'lead.message' => ['required', 'string', 'min:5', 'max:5000'],
            'lead.tags' => ['nullable', 'array', 'max:10'],
            'lead.tags.*' => ['string', 'max:50'],
        ];
    }
}
