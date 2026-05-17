<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class AddHoursPackRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'hours' => ['required', 'integer', 'min:1'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'reason' => ['required', 'string', 'max:200'],
            'dated_on' => ['nullable', 'date'],
            'source_lead_id' => ['nullable', 'integer', Rule::exists('leads', 'id')->whereNull('deleted_at')],
        ];
    }
}
