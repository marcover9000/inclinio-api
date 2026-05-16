<?php

namespace App\Modules\Shared\Http\Resources\Concerns;

use App\Modules\Shared\Domain\ValueObjects\Money;

/**
 * Serialitza un Money de forma uniforme a les API Resources.
 * Reutilitzat per ProjectResource i HoursPackResource.
 */
trait SerializesMoney
{
    /**
     * @return array{cents:int,currency:string,formatted:string}|null
     */
    protected function money(?Money $money): ?array
    {
        if ($money === null) {
            return null;
        }

        return [
            'cents' => $money->amountCents,
            'currency' => $money->currency,
            'formatted' => $money->format(),
        ];
    }
}
