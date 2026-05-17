<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('rejects an hourly pack #1 without hours/hourly_rate on project store', function () {
    $company = Company::factory()->create();
    $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'X', 'is_internal' => false, 'client_company_id' => $company->id,
        'pack' => ['billing_mode' => 'hourly', 'currency' => 'EUR', 'reason' => 'r'],
    ])->assertJsonValidationErrors(['pack.hours', 'pack.hourly_rate_cents']);
});

it('rejects a fixed pack #1 without price_cents on project store', function () {
    $company = Company::factory()->create();
    $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'X', 'is_internal' => false, 'client_company_id' => $company->id,
        'pack' => ['billing_mode' => 'fixed', 'currency' => 'EUR', 'reason' => 'r'],
    ])->assertJsonValidationErrors(['pack.price_cents']);
});

it('treats a pack with no billing_mode as fixed (price_cents required)', function () {
    $company = Company::factory()->create();
    $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'X', 'is_internal' => false, 'client_company_id' => $company->id,
        'pack' => ['hours' => 10, 'currency' => 'EUR', 'reason' => 'r'],
    ])->assertJsonValidationErrors(['pack.price_cents']);
});

it('rejects a bad billing_mode value', function () {
    $company = Company::factory()->create();
    $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'X', 'is_internal' => false, 'client_company_id' => $company->id,
        'pack' => ['billing_mode' => 'bogus', 'currency' => 'EUR', 'reason' => 'r'],
    ])->assertJsonValidationErrors(['pack.billing_mode']);
});

it('rejects an hourly ampliació without hours/rate on /packs', function () {
    $p = Project::factory()->create();
    $this->actingAs($this->admin)->postJson("/api/projects/{$p->id}/packs", [
        'billing_mode' => 'hourly', 'currency' => 'EUR', 'reason' => 'r',
    ])->assertJsonValidationErrors(['hours', 'hourly_rate_cents']);
});

it('rejects an hourly conversion pack without hours/rate on /leads/{lead}/project', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->won()->create(['person_id' => $person->id, 'company_id' => $company->id]);
    $this->actingAs($this->admin)->postJson("/api/leads/{$lead->id}/project", [
        'mode' => 'new', 'name' => 'X',
        'pack' => ['billing_mode' => 'hourly', 'currency' => 'EUR', 'reason' => 'r'],
    ])->assertJsonValidationErrors(['pack.hours', 'pack.hourly_rate_cents']);
});
