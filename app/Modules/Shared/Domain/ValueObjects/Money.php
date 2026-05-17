<?php

namespace App\Modules\Shared\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value object monetari immutable. Quantitat en cèntims (int, mai float)
 * + moneda ISO de 3 lletres. Primitiva compartida i AÏLLADA: és la peça
 * que el refactor fort de facturació (~6-12 mesos) podrà substituir sense
 * tocar la resta del domini. No barrejar amb cobraments reals (Billing).
 */
final class Money
{
    private function __construct(
        public readonly int $amountCents,
        public readonly string $currency,
    ) {
    }

    public static function fromCents(int $amountCents, string $currency = 'EUR'): self
    {
        $currency = strtoupper(trim($currency));
        if (mb_strlen($currency) !== 3) {
            throw new InvalidArgumentException('La moneda ha de ser un codi ISO de 3 lletres.');
        }

        return new self($amountCents, $currency);
    }

    public static function zero(string $currency = 'EUR'): self
    {
        return self::fromCents(0, $currency);
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountCents + $other->amountCents, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountCents - $other->amountCents, $this->currency);
    }

    /** Escala per un factor enter. Factors negatius són vàlids (abonaments). */
    public function multiply(int $factor): self
    {
        return new self($this->amountCents * $factor, $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amountCents === $other->amountCents
            && $this->currency === $other->currency;
    }

    public function isZero(): bool
    {
        return $this->amountCents === 0;
    }

    public function format(): string
    {
        return number_format($this->amountCents / 100, 2, ',', '.').' '.$this->currency;
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(sprintf(
                'No es poden operar monedes diferents: %s vs %s.',
                $this->currency,
                $other->currency,
            ));
        }
    }
}
