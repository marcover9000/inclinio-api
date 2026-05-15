<?php

namespace App\Modules\Shared\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base per a requests que només requereixen un usuari autenticat.
 * L'autorització fina (rols/policies) es resol a les rutes/middleware.
 */
abstract class AuthenticatedFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
