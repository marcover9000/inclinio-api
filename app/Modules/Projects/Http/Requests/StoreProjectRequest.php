<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'is_internal' => ['sometimes', 'boolean'],
            'client_company_id' => ['nullable', 'integer', Rule::exists('companies', 'id')->whereNull('deleted_at')],
            'client_person_id' => ['nullable', 'integer', Rule::exists('people', 'id')->whereNull('deleted_at')],
            'shadow_rate_override_cents' => ['nullable', 'integer', 'min:0'],
            'shadow_rate_override_currency' => ['nullable', 'required_with:shadow_rate_override_cents', 'string', 'size:3'],
            'started_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'pack' => ['nullable', 'array'],
            'pack.hours' => ['required_with:pack', 'integer', 'min:1'],
            'pack.price_cents' => ['required_with:pack', 'integer', 'min:0'],
            'pack.currency' => ['required_with:pack', 'string', 'size:3'],
            'pack.reason' => ['required_with:pack', 'string', 'max:200'],
            'pack.dated_on' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $internal = $this->boolean('is_internal');
            $hasClient = $this->filled('client_company_id') || $this->filled('client_person_id');

            if (! $internal && ! $hasClient) {
                $v->errors()->add('client_company_id', 'Un projecte no intern necessita un client (empresa o persona).');
            }
            if ($internal && $hasClient) {
                $v->errors()->add('is_internal', 'Un projecte intern no pot tenir client.');
            }
        });
    }
}
