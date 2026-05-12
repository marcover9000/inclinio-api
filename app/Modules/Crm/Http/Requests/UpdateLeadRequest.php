<?php

namespace App\Modules\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['sometimes', 'string', 'min:5', 'max:5000'],
            'tags' => ['sometimes', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
