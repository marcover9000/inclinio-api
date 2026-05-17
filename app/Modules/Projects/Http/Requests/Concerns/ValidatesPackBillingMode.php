<?php

namespace App\Modules\Projects\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;

/**
 * Validació condicional del mode de facturació d'un pack.
 * Compat enrere: si no ve `billing_mode` → 'fixed'.
 *  - fixed  → cal {prefix}price_cents
 *  - hourly → cal {prefix}hours i {prefix}hourly_rate_cents
 * $prefix és '' (pack pla, p.ex. AddHoursPack) o 'pack.' (pack niat).
 */
trait ValidatesPackBillingMode
{
    protected function validatePackBillingMode(Validator $validator, string $prefix = ''): void
    {
        $mode = $this->input($prefix.'billing_mode', 'fixed');

        if ($mode === 'hourly') {
            if (! $this->filled($prefix.'hours')) {
                $validator->errors()->add($prefix.'hours', 'Les hores són obligatòries en mode "per hores".');
            }
            if (! $this->filled($prefix.'hourly_rate_cents')) {
                $validator->errors()->add($prefix.'hourly_rate_cents', 'La tarifa €/hora és obligatòria en mode "per hores".');
            }
        } else {
            if (! $this->filled($prefix.'price_cents')) {
                $validator->errors()->add($prefix.'price_cents', 'El preu tancat és obligatori en mode "preu tancat".');
            }
        }
    }
}
