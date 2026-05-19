<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;

class DashboardTimeRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
    }
}
