<?php

namespace App\Modules\Contacts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:32'],
            'position' => ['nullable', 'string', 'max:100'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ];
    }
}
