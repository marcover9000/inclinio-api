<?php

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Projects\Application\Actions\AddHoursPack;
use App\Modules\Projects\Application\Actions\CreateProject;
use App\Modules\Projects\Application\Actions\ConvertLeadToProject;
use App\Modules\Projects\Domain\Enums\BillingMode;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('AddHoursPack hourly computes price = hours x rate', function () {
    $p = Project::factory()->create();
    $pack = app(AddHoursPack::class)($p, [
        'billing_mode' => BillingMode::Hourly,
        'hours' => 40,
        'hourly_rate' => Money::fromCents(2000, 'EUR'),
        'reason' => 'Ampliació per hores',
    ]);
    expect($pack->billing_mode)->toEqual(BillingMode::Hourly)
        ->and($pack->hours)->toBe(40)
        ->and($pack->hourly_rate->amountCents)->toBe(2000)
        ->and($pack->price->amountCents)->toBe(80000);
});

it('AddHoursPack accepts billing_mode as a string', function () {
    $p = Project::factory()->create();
    $pack = app(AddHoursPack::class)($p, [
        'billing_mode' => 'hourly',
        'hours' => 10,
        'hourly_rate' => Money::fromCents(5000, 'EUR'),
        'reason' => 'x',
    ]);
    expect($pack->billing_mode)->toEqual(BillingMode::Hourly)
        ->and($pack->price->amountCents)->toBe(50000);
});

it('AddHoursPack fixed keeps the given price and allows null hours', function () {
    $p = Project::factory()->create();
    $pack = app(AddHoursPack::class)($p, [
        'billing_mode' => BillingMode::Fixed,
        'price' => Money::fromCents(500000, 'EUR'),
        'reason' => 'Preu tancat sense hores',
    ]);
    expect($pack->billing_mode)->toEqual(BillingMode::Fixed)
        ->and($pack->hours)->toBeNull()
        ->and($pack->hourly_rate)->toBeNull()
        ->and($pack->price->amountCents)->toBe(500000);
});

it('AddHoursPack defaults to fixed when no billing_mode (backward compat)', function () {
    $p = Project::factory()->create();
    $pack = app(AddHoursPack::class)($p, [
        'hours' => 12,
        'price' => Money::fromCents(120000, 'EUR'),
        'reason' => 'Compat',
    ]);
    expect($pack->billing_mode)->toEqual(BillingMode::Fixed)
        ->and($pack->hours)->toBe(12)
        ->and($pack->price->amountCents)->toBe(120000);
});

it('CreateProject creates an hourly pack #1 with computed price', function () {
    $company = Company::factory()->create();
    $project = app(CreateProject::class)([
        'name' => 'Estadia API',
        'client_company_id' => $company->id,
        'pack' => [
            'billing_mode' => BillingMode::Hourly,
            'hours' => 50,
            'hourly_rate' => Money::fromCents(2000, 'EUR'),
            'reason' => 'Venda inicial',
        ],
    ]);
    $pack = $project->hoursPacks()->first();
    expect($pack->billing_mode)->toEqual(BillingMode::Hourly)
        ->and($pack->price->amountCents)->toBe(100000)
        ->and($project->budgetedHours())->toBe(50);
});

it('ConvertLeadToProject (new) forwards an hourly pack and stamps the lead', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->won()->create(['person_id' => $person->id, 'company_id' => $company->id]);

    $project = app(ConvertLeadToProject::class)($lead, [
        'mode' => 'new',
        'name' => 'Des del lead',
        'pack' => [
            'billing_mode' => BillingMode::Hourly,
            'hours' => 20,
            'hourly_rate' => Money::fromCents(3000, 'EUR'),
            'reason' => 'Venda inicial',
        ],
    ]);
    $pack = $project->hoursPacks()->first();
    expect($pack->billing_mode)->toEqual(BillingMode::Hourly)
        ->and($pack->price->amountCents)->toBe(60000)
        ->and($pack->source_lead_id)->toBe($lead->id);
});
