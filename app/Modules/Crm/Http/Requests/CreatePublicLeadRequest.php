<?php

namespace App\Modules\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePublicLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Honeypot: if _hp is filled, skip all validation and let the controller
        // detect it and return a silent 201 without persisting anything.
        if (!empty($this->input('_hp'))) {
            return [];
        }

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:32'],
            'position' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:4', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
            '_hp' => ['nullable', 'string'],
        ];
    }
}
