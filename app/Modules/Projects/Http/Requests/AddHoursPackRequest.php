<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Projects\Http\Requests\Concerns\ValidatesPackBillingMode;
use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class AddHoursPackRequest extends AuthenticatedFormRequest
{
    use ValidatesPackBillingMode;

    public function rules(): array
    {
        return [
            'billing_mode' => ['sometimes', Rule::in(['fixed', 'hourly'])],
            'hours' => ['nullable', 'integer', 'min:1'],
            'price_cents' => ['nullable', 'integer', 'min:1'],
            'hourly_rate_cents' => ['nullable', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            'reason' => ['required', 'string', 'max:200'],
            'dated_on' => ['nullable', 'date'],
            'source_lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->whereNull('deleted_at')],
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(fn ($v) => $this->validatePackBillingMode($v, ''));
    }
}
