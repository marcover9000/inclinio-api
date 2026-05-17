<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

function wonLead(): Lead
{
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);

    return Lead::factory()->won()->create(['person_id' => $person->id, 'company_id' => $company->id]);
}

it('requires authentication', function () {
    $lead = wonLead();
    $this->postJson("/api/leads/{$lead->id}/project", [])->assertUnauthorized();
});

it('mode=new creates a project + pack #1 from a won lead', function () {
    $lead = wonLead();
    $r = $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'new',
        'name' => 'Projecte des de lead',
        'pack' => ['hours' => 30, 'price_cents' => 360000, 'currency' => 'EUR', 'reason' => 'Venda inicial'],
    ]);
    $r->assertCreated();
    expect($r->json('data.client_company_id'))->toBe($lead->company_id)
        ->and($r->json('data.budgeted_hours'))->toBe(30)
        ->and($r->json('data.hours_packs.0.source_lead_id'))->toBe($lead->id);
});

it('mode=extend adds a pack to an existing project and reopens it', function () {
    $lead = wonLead();
    $existing = Project::factory()->withStatus(ProjectStatus::Done)->create();
    $r = $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'extend',
        'project_id' => $existing->id,
        'pack' => ['hours' => 12, 'price_cents' => 144000, 'currency' => 'EUR', 'reason' => 'Ampliació'],
    ]);
    $r->assertCreated()
        ->assertJsonPath('data.id', $existing->id)
        ->assertJsonPath('data.status', 'active');
});

it('returns 422 when the lead is not won', function () {
    $lead = Lead::factory()->withStatus(LeadStatus::New)->create();
    $r = $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'new', 'name' => 'X',
        'pack' => ['hours' => 1, 'price_cents' => 100, 'currency' => 'EUR', 'reason' => 'x'],
    ]);
    $r->assertUnprocessable();
    expect($r->json('message'))->toContain("won");
});

it('validates mode-dependent fields', function () {
    $lead = wonLead();
    $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'new',
        'pack' => ['hours' => 1, 'price_cents' => 100, 'currency' => 'EUR', 'reason' => 'x'],
    ])->assertJsonValidationErrors(['name']);

    $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'extend',
        'pack' => ['hours' => 1, 'price_cents' => 100, 'currency' => 'EUR', 'reason' => 'x'],
    ])->assertJsonValidationErrors(['project_id']);
});
