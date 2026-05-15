<?php

namespace App\Modules\Crm\Http\Requests;

use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;

class StoreLeadNoteRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:1', 'max:10000']];
    }
}
