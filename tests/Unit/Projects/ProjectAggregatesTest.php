<?php

use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('totalPrice is zero EUR with no packs', function () {
    $p = Project::factory()->create();
    expect($p->totalPrice()->isZero())->toBeTrue()
        ->and($p->totalPrice()->currency)->toBe('EUR')
        ->and($p->budgetedHours())->toBe(0);
});

it('totalPrice and budgetedHours sum all packs (pack #1 + ampliacions)', function () {
    $p = Project::factory()->create();
    HoursPack::factory()->create([
        'project_id' => $p->id, 'hours' => 40, 'price' => Money::fromCents(400000, 'EUR'),
    ]);
    HoursPack::factory()->create([
        'project_id' => $p->id, 'hours' => 15, 'price' => Money::fromCents(150000, 'EUR'),
    ]);

    $p->refresh();
    expect($p->totalPrice()->amountCents)->toBe(550000)
        ->and($p->budgetedHours())->toBe(55);
});
