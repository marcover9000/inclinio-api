<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Application\Actions\ConvertLeadToProject;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Exceptions\LeadNotConvertible;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function wonLeadWithCompany(): Lead
{
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);

    return Lead::factory()->won()->create([
        'person_id' => $person->id,
        'company_id' => $company->id,
    ]);
}

it('refuses to convert a non-won lead', function () {
    $lead = Lead::factory()->withStatus(LeadStatus::New)->create();
    expect(fn () => app(ConvertLeadToProject::class)($lead, [
        'mode' => 'new', 'name' => 'X',
        'pack' => ['hours' => 1, 'price' => Money::fromCents(100, 'EUR'), 'reason' => 'x'],
    ]))->toThrow(LeadNotConvertible::class);
});

it('mode=new creates a project + pack #1 with source_lead_id and the lead client', function () {
    $lead = wonLeadWithCompany();
    $project = app(ConvertLeadToProject::class)($lead, [
        'mode' => 'new',
        'name' => 'Projecte des de lead',
        'pack' => ['hours' => 30, 'price' => Money::fromCents(360000, 'EUR'), 'reason' => 'Venda inicial'],
    ]);

    $pack = $project->hoursPacks()->first();
    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->status)->toEqual(ProjectStatus::Active)
        ->and($project->client_company_id)->toBe($lead->company_id)
        ->and($project->client_person_id)->toBeNull()
        ->and($pack->source_lead_id)->toBe($lead->id)
        ->and($pack->hours)->toBe(30);
});

it('mode=new with a company-less lead links the person as client', function () {
    $person = Person::factory()->create(['company_id' => null]);
    $lead = Lead::factory()->won()->create(['person_id' => $person->id, 'company_id' => null]);

    $project = app(ConvertLeadToProject::class)($lead, [
        'mode' => 'new', 'name' => 'Freelance directe',
        'pack' => ['hours' => 10, 'price' => Money::fromCents(120000, 'EUR'), 'reason' => 'Venda inicial'],
    ]);

    expect($project->client_company_id)->toBeNull()
        ->and($project->client_person_id)->toBe($person->id);
});

it('mode=extend adds a pack to an existing project and reopens it if done', function () {
    $lead = wonLeadWithCompany();
    $existing = Project::factory()->withStatus(ProjectStatus::Done)->create();

    $project = app(ConvertLeadToProject::class)($lead, [
        'mode' => 'extend',
        'project_id' => $existing->id,
        'pack' => ['hours' => 12, 'price' => Money::fromCents(144000, 'EUR'), 'reason' => 'Ampliació'],
    ]);

    expect($project->id)->toBe($existing->id)
        ->and($project->status)->toEqual(ProjectStatus::Active)
        ->and($project->hoursPacks()->where('source_lead_id', $lead->id)->count())->toBe(1);
});
