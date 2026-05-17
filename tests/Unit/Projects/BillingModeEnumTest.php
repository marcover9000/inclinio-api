<?php

use App\Modules\Projects\Domain\Enums\BillingMode;

it('has fixed and hourly cases with string values', function () {
    expect(BillingMode::Fixed->value)->toBe('fixed')
        ->and(BillingMode::Hourly->value)->toBe('hourly')
        ->and(BillingMode::cases())->toHaveCount(2);
});

it('exposes a non-empty Catalan label for every case', function () {
    expect(BillingMode::Fixed->label())->toBe('Preu tancat')
        ->and(BillingMode::Hourly->label())->toBe('Per hores');
    foreach (BillingMode::cases() as $m) {
        expect($m->label())->toBeString()->not->toBe('');
    }
});
