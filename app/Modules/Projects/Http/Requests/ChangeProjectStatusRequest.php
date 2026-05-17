<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rule;

class ChangeProjectStatusRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(ProjectStatus::cases(), 'value'))],
        ];
    }
}
