<?php

use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Shared\Domain\Settings;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('consumedHours sums minutes/60; overrun null without budget', function () {
    $p = Project::factory()->create();
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 90]);
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 30]);

    $p->refresh();
    expect($p->consumedHours())->toBe(2.0)
        ->and($p->overrunPercent())->toBeNull();
});

it('overrunPercent uses budgeted hours', function () {
    $p = Project::factory()->create();
    HoursPack::factory()->create(['project_id' => $p->id, 'hours' => 10]);
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 12 * 60]);

    $p->refresh();
    expect($p->overrunPercent())->toBe(20.0); // (12-10)/10*100
});

it('shadowRate uses project override, else global', function () {
    $global = Project::factory()->create();
    expect($global->shadowRate()->amountCents)->toBe(Settings::shadowRate()->amountCents);

    $override = Project::factory()->create(['shadow_rate_override' => Money::fromCents(5000, 'EUR')]);
    expect($override->shadowRate()->amountCents)->toBe(5000);
});

it('theoreticalCost = consumedHours x shadowRate (rounded cents)', function () {
    $p = Project::factory()->create(['shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 90]); // 1.5h
    $p->refresh();
    expect($p->theoreticalCost()->amountCents)->toBe(4500); // 1.5 * 3000
});

it('realMargin: null for internal, price - cost for client', function () {
    $internal = Project::factory()->internal()->create(['shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    TimeEntry::factory()->create(['project_id' => $internal->id, 'minutes' => 60]);
    $internal->refresh();
    expect($internal->realMargin())->toBeNull()
        ->and($internal->theoreticalCost()->amountCents)->toBe(3000);

    $client = Project::factory()->create(['shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    HoursPack::factory()->create(['project_id' => $client->id, 'price' => Money::fromCents(100000, 'EUR'), 'hours' => 10]);
    TimeEntry::factory()->create(['project_id' => $client->id, 'minutes' => 600]); // 10h -> cost 30000
    $client->refresh();
    expect($client->realMargin()->amountCents)->toBe(70000); // 100000 - 30000
});
