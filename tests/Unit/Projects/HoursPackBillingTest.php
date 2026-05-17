<?php

use App\Modules\Projects\Domain\Enums\BillingMode;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('defaults to fixed mode with null hourly_rate', function () {
    $fresh = HoursPack::factory()->create()->fresh();
    expect($fresh->billing_mode)->toEqual(BillingMode::Fixed)
        ->and($fresh->hourly_rate)->toBeNull()
        ->and($fresh->price)->toBeInstanceOf(Money::class);
});

it('hourly() state sets mode, rate and price = hours x rate', function () {
    $fresh = HoursPack::factory()->hourly(40, 2000)->create()->fresh();
    expect($fresh->billing_mode)->toEqual(BillingMode::Hourly)
        ->and($fresh->hours)->toBe(40)
        ->and($fresh->hourly_rate)->toBeInstanceOf(Money::class)
        ->and($fresh->hourly_rate->amountCents)->toBe(2000)
        ->and($fresh->price->amountCents)->toBe(80000);
});

it('allows a fixed pack with null hours', function () {
    expect(HoursPack::factory()->create(['hours' => null])->fresh()->hours)->toBeNull();
});
