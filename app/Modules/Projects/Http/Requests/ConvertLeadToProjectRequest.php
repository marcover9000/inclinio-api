<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Projects\Http\Requests\Concerns\ValidatesPackBillingMode;
use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadToProjectRequest extends AuthenticatedFormRequest
{
    use ValidatesPackBillingMode;

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['new', 'extend'])],
            'name' => ['required_if:mode,new', 'string', 'max:200'],
            'project_id' => ['required_if:mode,extend', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'pack' => ['required', 'array'],
            'pack.billing_mode' => ['sometimes', Rule::in(['fixed', 'hourly'])],
            'pack.hours' => ['nullable', 'integer', 'min:1'],
            'pack.price_cents' => ['nullable', 'integer', 'min:1'],
            'pack.hourly_rate_cents' => ['nullable', 'integer', 'min:1'],
            'pack.currency' => ['required', 'string', 'size:3'],
            'pack.reason' => ['required', 'string', 'max:200'],
            'pack.dated_on' => ['nullable', 'date'],
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(fn ($v) => $this->validatePackBillingMode($v, 'pack.'));
    }
}
