<?php

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('casts price to Money and belongs to a project', function () {
    $pack = HoursPack::factory()->create([
        'price' => Money::fromCents(500000, 'EUR'),
        'hours' => 40,
    ]);

    $fresh = $pack->fresh();
    expect($fresh->price)->toBeInstanceOf(Money::class)
        ->and($fresh->price->amountCents)->toBe(500000)
        ->and($fresh->hours)->toBe(40)
        ->and($fresh->project)->toBeInstanceOf(Project::class);
});

it('can reference a source lead, or none', function () {
    $lead = Lead::factory()->won()->create();
    $withLead = HoursPack::factory()->fromLead($lead)->create();
    $withoutLead = HoursPack::factory()->create();

    expect($withLead->sourceLead)->toBeInstanceOf(Lead::class)
        ->and($withLead->source_lead_id)->toBe($lead->id)
        ->and($withoutLead->source_lead_id)->toBeNull();
});

it('soft-deletes', function () {
    $pack = HoursPack::factory()->create();
    $pack->delete();
    expect(HoursPack::count())->toBe(0)
        ->and(HoursPack::withTrashed()->count())->toBe(1);
});
