<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('creates a project with an hourly pack #1 (price computed)', function () {
    $company = Company::factory()->create();
    $r = $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'Estadia API', 'is_internal' => false, 'client_company_id' => $company->id,
        'pack' => ['billing_mode' => 'hourly', 'hours' => 40, 'hourly_rate_cents' => 2000, 'currency' => 'EUR', 'reason' => 'Venda inicial'],
    ]);
    $r->assertCreated();
    expect($r->json('data.budgeted_hours'))->toBe(40)
        ->and($r->json('data.total_price.cents'))->toBe(80000)
        ->and($r->json('data.hours_packs.0.billing_mode'))->toBe('hourly')
        ->and($r->json('data.hours_packs.0.hourly_rate.cents'))->toBe(2000);
});

it('creates a project with a fixed pack #1 without hours', function () {
    $company = Company::factory()->create();
    $r = $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'Tancat', 'is_internal' => false, 'client_company_id' => $company->id,
        'pack' => ['billing_mode' => 'fixed', 'price_cents' => 500000, 'currency' => 'EUR', 'reason' => 'Preu tancat'],
    ]);
    $r->assertCreated();
    expect($r->json('data.budgeted_hours'))->toBe(0)
        ->and($r->json('data.total_price.cents'))->toBe(500000)
        ->and($r->json('data.hours_packs.0.billing_mode'))->toBe('fixed')
        ->and($r->json('data.hours_packs.0.hourly_rate'))->toBeNull();
});

it('adds an hourly ampliació via /packs (price computed, reopens done)', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Done)->create();
    $r = $this->actingAs($this->admin)->postJson("/api/projects/{$p->id}/packs", [
        'billing_mode' => 'hourly', 'hours' => 10, 'hourly_rate_cents' => 5000, 'currency' => 'EUR', 'reason' => 'Ampliació',
    ]);
    $r->assertCreated()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.total_price.cents', 50000)
        ->assertJsonPath('data.hours_packs.0.billing_mode', 'hourly');
});

it('converts a won lead to a project with an hourly pack #1', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->won()->create(['person_id' => $person->id, 'company_id' => $company->id]);
    $r = $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'new', 'name' => 'Des del lead',
        'pack' => ['billing_mode' => 'hourly', 'hours' => 20, 'hourly_rate_cents' => 3000, 'currency' => 'EUR', 'reason' => 'Venda inicial'],
    ]);
    $r->assertCreated();
    expect($r->json('data.total_price.cents'))->toBe(60000)
        ->and($r->json('data.hours_packs.0.billing_mode'))->toBe('hourly')
        ->and($r->json('data.hours_packs.0.source_lead_id'))->toBe($lead->id);
});
