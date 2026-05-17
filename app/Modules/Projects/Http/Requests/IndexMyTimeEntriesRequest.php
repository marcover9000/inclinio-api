<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class IndexMyTimeEntriesRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'task_id' => ['nullable', 'integer', Rule::exists('tasks', 'id')->whereNull('deleted_at')],
        ];
    }
}
