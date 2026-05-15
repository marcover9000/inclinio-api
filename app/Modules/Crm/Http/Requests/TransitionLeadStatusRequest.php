<?php

namespace App\Modules\Crm\Http\Requests;

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Illuminate\Validation\Rules\Enum;

class TransitionLeadStatusRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(LeadStatus::class)],
        ];
    }
}
