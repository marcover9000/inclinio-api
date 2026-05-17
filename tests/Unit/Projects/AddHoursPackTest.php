<?php

use App\Modules\Projects\Application\Actions\AddHoursPack;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('appends a pack to an active project without changing status', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Active)->create();
    $pack = app(AddHoursPack::class)($p, [
        'hours' => 10,
        'price' => Money::fromCents(120000, 'EUR'),
        'reason' => 'Ampliació SEO',
    ]);

    expect($pack)->toBeInstanceOf(HoursPack::class)
        ->and($pack->project_id)->toBe($p->id)
        ->and($p->fresh()->status)->toEqual(ProjectStatus::Active);
});

it('reopens a done project to active when an ampliació is added', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Done)->create();
    app(AddHoursPack::class)($p, [
        'hours' => 8,
        'price' => Money::fromCents(96000, 'EUR'),
        'reason' => 'Ampliació',
    ]);
    expect($p->fresh()->status)->toEqual(ProjectStatus::Active);
});

it('reopens an archived project too', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Archived)->create();
    app(AddHoursPack::class)($p, [
        'hours' => 5,
        'price' => Money::fromCents(60000, 'EUR'),
        'reason' => 'Ampliació',
    ]);
    expect($p->fresh()->status)->toEqual(ProjectStatus::Active);
});

it('records source_lead_id when given', function () {
    $p = Project::factory()->create();
    $pack = app(AddHoursPack::class)($p, [
        'hours' => 5, 'price' => Money::fromCents(60000, 'EUR'),
        'reason' => 'Ampliació via lead', 'source_lead_id' => null,
    ]);
    expect($pack->source_lead_id)->toBeNull();
});
