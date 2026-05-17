<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:200'],
            'is_internal' => ['sometimes', 'boolean'],
            'client_company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')->whereNull('deleted_at')],
            'client_person_id' => ['nullable', 'integer', Rule::exists('people', 'id')->whereNull('deleted_at')],
            'shadow_rate_override_cents' => ['nullable', 'integer', 'min:0'],
            'shadow_rate_override_currency' => ['nullable', 'required_with:shadow_rate_override_cents', 'string', 'size:3'],
            'started_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
        ];
    }
}
