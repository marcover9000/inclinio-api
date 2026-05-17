<?php

use App\Modules\Shared\Domain\Settings;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('reads the seeded global shadow rate', function () {
    expect(Settings::shadowRate()->amountCents)->toBe(3000)
        ->and(Settings::shadowRate()->currency)->toBe('EUR');
});

it('updates the global shadow rate (upsert)', function () {
    Settings::setShadowRate(Money::fromCents(4500, 'EUR'));
    expect(Settings::shadowRate()->amountCents)->toBe(4500);

    Settings::setShadowRate(Money::fromCents(5000, 'EUR'));
    expect(Settings::shadowRate()->amountCents)->toBe(5000);
});
