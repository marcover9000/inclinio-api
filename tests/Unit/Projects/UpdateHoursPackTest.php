<?php

use App\Modules\Projects\Application\Actions\UpdateHoursPack;
use App\Modules\Projects\Domain\Enums\BillingMode;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('updates a fixed pack\'s price, hours and reason without touching the project status', function () {
    $project = Project::factory()->withStatus(ProjectStatus::Done)->create();
    $pack = HoursPack::factory()->create(['project_id' => $project->id]);

    (new UpdateHoursPack)($pack, [
        'billing_mode' => 'fixed',
        'price' => Money::fromCents(750000, 'EUR'),
        'hours' => null,
        'reason' => 'Correcció',
    ]);

    $fresh = $pack->fresh();
    expect($fresh->price->amountCents)->toBe(750000)
        ->and($fresh->reason)->toBe('Correcció')
        ->and($project->fresh()->status)->toEqual(ProjectStatus::Done);
});

it('switches a pack to hourly and recomputes the stored price', function () {
    $project = Project::factory()->create();
    $pack = HoursPack::factory()->create(['project_id' => $project->id]);

    (new UpdateHoursPack)($pack, [
        'billing_mode' => 'hourly',
        'hours' => 10,
        'hourly_rate' => Money::fromCents(5000, 'EUR'),
        'reason' => 'A hores',
    ]);

    $fresh = $pack->fresh();
    expect($fresh->billing_mode)->toEqual(BillingMode::Hourly)
        ->and($fresh->hours)->toBe(10)
        ->and($fresh->hourly_rate->amountCents)->toBe(5000)
        ->and($fresh->price->amountCents)->toBe(50000);
});

it('switches an hourly pack back to fixed clearing hourly_rate', function () {
    $project = Project::factory()->create();
    $pack = HoursPack::factory()->hourly()->create(['project_id' => $project->id]);

    (new UpdateHoursPack)($pack, [
        'billing_mode' => 'fixed',
        'price' => Money::fromCents(300000, 'EUR'),
        'hours' => null,
        'reason' => 'Preu tancat',
    ]);

    $fresh = $pack->fresh();
    expect($fresh->billing_mode)->toEqual(BillingMode::Fixed)
        ->and($fresh->hourly_rate)->toBeNull()
        ->and($fresh->price->amountCents)->toBe(300000);
});

it('does not change project_id or source_lead_id', function () {
    $projectA = Project::factory()->create();
    $lead = \App\Modules\Crm\Domain\Models\Lead::factory()->won()->create();
    $pack = HoursPack::factory()->fromLead($lead)->create(['project_id' => $projectA->id]);

    $originalProjectId = $pack->project_id;
    $originalLeadId = $pack->source_lead_id;

    (new UpdateHoursPack)($pack, [
        'billing_mode' => 'fixed',
        'price' => Money::fromCents(100000, 'EUR'),
        'hours' => null,
        'reason' => 'Actualització',
    ]);

    $fresh = $pack->fresh();
    expect($fresh->project_id)->toBe($originalProjectId)
        ->and($fresh->source_lead_id)->toBe($originalLeadId);
});
