<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class ConvertLeadToProjectRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['new', 'extend'])],
            'name' => ['required_if:mode,new', 'string', 'max:200'],
            'project_id' => ['required_if:mode,extend', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'pack' => ['required', 'array'],
            'pack.hours' => ['required', 'integer', 'min:1'],
            'pack.price_cents' => ['required', 'integer', 'min:0'],
            'pack.currency' => ['required', 'string', 'size:3'],
            'pack.reason' => ['required', 'string', 'max:200'],
            'pack.dated_on' => ['nullable', 'date'],
        ];
    }
}
