<?php

namespace App\Modules\Shared\Infrastructure\Casts;

use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Mapeja un atribut `Money` a dues columnes: `{key}_cents` i `{key}_currency`.
 * Null-safe: si `{key}_cents` és null, l'atribut és null. Mai float.
 *
 * @implements CastsAttributes<Money|null, Money|null>
 */
class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        $cents = $attributes["{$key}_cents"] ?? null;
        if ($cents === null) {
            return null;
        }

        return Money::fromCents((int) $cents, $attributes["{$key}_currency"] ?? 'EUR');
    }

    /**
     * @return array<string, int|string|null>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return ["{$key}_cents" => null, "{$key}_currency" => null];
        }

        if (! $value instanceof Money) {
            throw new InvalidArgumentException("L'atribut {$key} ha de ser una instància de Money o null.");
        }

        return [
            "{$key}_cents" => $value->amountCents,
            "{$key}_currency" => $value->currency,
        ];
    }
}
