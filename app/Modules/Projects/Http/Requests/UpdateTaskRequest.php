<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'status' => ['sometimes', Rule::in(['todo', 'doing', 'done'])],
            'estimate_hours' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
