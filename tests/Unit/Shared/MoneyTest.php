<?php

use App\Modules\Shared\Domain\ValueObjects\Money;

it('builds from cents and exposes amount + currency', function () {
    $m = Money::fromCents(150000, 'eur');
    expect($m->amountCents)->toBe(150000)
        ->and($m->currency)->toBe('EUR'); // normalitzat a majúscules
});

it('rejects a non 3-letter currency', function () {
    expect(fn () => Money::fromCents(100, 'EURO'))
        ->toThrow(InvalidArgumentException::class);
});

it('builds a zero amount', function () {
    expect(Money::zero('EUR')->isZero())->toBeTrue();
});

it('adds two amounts of the same currency immutably', function () {
    $a = Money::fromCents(1000, 'EUR');
    $b = Money::fromCents(2500, 'EUR');
    $sum = $a->add($b);
    expect($sum->amountCents)->toBe(3500)
        ->and($a->amountCents)->toBe(1000); // original intacte
});

it('refuses to add different currencies', function () {
    expect(fn () => Money::fromCents(100, 'EUR')->add(Money::fromCents(100, 'USD')))
        ->toThrow(InvalidArgumentException::class);
});

it('multiplies by an integer factor', function () {
    expect(Money::fromCents(500, 'EUR')->multiply(3)->amountCents)->toBe(1500);
});

it('compares value equality', function () {
    expect(Money::fromCents(100, 'EUR')->equals(Money::fromCents(100, 'EUR')))->toBeTrue()
        ->and(Money::fromCents(100, 'EUR')->equals(Money::fromCents(200, 'EUR')))->toBeFalse()
        ->and(Money::fromCents(100, 'EUR')->equals(Money::fromCents(100, 'USD')))->toBeFalse();
});

it('formats with comma decimals and currency suffix', function () {
    expect(Money::fromCents(123456, 'EUR')->format())->toBe('1.234,56 EUR');
});

it('formats negative amounts correctly', function () {
    expect(Money::fromCents(-150, 'EUR')->format())->toBe('-1,50 EUR');
});

it('subtracts two money of the same currency', function () {
    $r = \App\Modules\Shared\Domain\ValueObjects\Money::fromCents(1000, 'EUR')
        ->subtract(\App\Modules\Shared\Domain\ValueObjects\Money::fromCents(300, 'EUR'));
    expect($r->amountCents)->toBe(700)->and($r->currency)->toBe('EUR');
});
