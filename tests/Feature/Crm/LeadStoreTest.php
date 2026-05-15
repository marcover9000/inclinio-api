<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('requires authentication', function () {
    $this->postJson('/api/leads', [])->assertUnauthorized();
});

it('creates lead with person only (no company)', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['first_name' => 'Marc', 'email' => 'marc@test.com'],
        'lead' => ['message' => 'hola hola hola'],
    ])->assertCreated();

    expect(Lead::count())->toEqual(1);
    expect(Person::count())->toEqual(1);
    expect(Company::count())->toEqual(0);
});

it('creates lead with person and new company', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['first_name' => 'Marc', 'email' => 'marc@test.com'],
        'company' => ['name' => 'Acme', 'vat' => 'B12345'],
        'lead' => ['message' => 'hola hola', 'tags' => ['web']],
    ])->assertCreated();

    expect(Company::count())->toEqual(1);
    expect(Lead::first()->tags)->toEqual(['web']);
});

it('reuses existing company by name', function () {
    Company::factory()->create(['name' => 'Acme']);
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['first_name' => 'Marc', 'email' => 'marc@test.com'],
        'company' => ['name' => 'acme'],
        'lead' => ['message' => 'hola hola'],
    ])->assertCreated();
    expect(Company::count())->toEqual(1);
});

it('forces source to manual server-side', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['first_name' => 'Marc', 'email' => 'marc@test.com'],
        'lead' => ['message' => 'hola hola'],
        'source' => 'web_form',
    ])->assertCreated();
    expect(Lead::first()->source)->toEqual(LeadSource::Manual);
});

it('validates person.first_name required', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['email' => 'a@b.com'],
        'lead' => ['message' => 'hola hola'],
    ])->assertJsonValidationErrors(['person.first_name']);
});

it('validates message required', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['first_name' => 'X'],
    ])->assertJsonValidationErrors(['lead.message']);
});

it('returns inflated LeadResource with person and company', function () {
    $resp = $this->actingAs($this->admin)->postJson('/api/leads', [
        'person' => ['first_name' => 'Marc', 'email' => 'marc@test.com'],
        'company' => ['name' => 'Acme'],
        'lead' => ['message' => 'hola hola'],
    ])->assertCreated();
    $resp->assertJsonStructure(['data' => ['id', 'status', 'source', 'message', 'tags', 'person' => ['id', 'first_name'], 'company' => ['id', 'name']]]);
});

it('accepts person_id pointing to an existing Person and reuses it', function () {
    $company = Company::factory()->create(['name' => 'Acme']);
    $existing = Person::factory()->create([
        'first_name' => 'Existing',
        'company_id' => $company->id,
    ]);

    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person_id' => $existing->id,
        'lead' => ['message' => 'reuse existing person'],
    ])->assertCreated();

    // No new Person created — total still 1
    expect(Person::count())->toEqual(1);
    $lead = Lead::first();
    expect($lead->person_id)->toEqual($existing->id);
    // company_id inherits from the resolved Person
    expect($lead->company_id)->toEqual($company->id);
});

it('rejects when neither person_id nor person is provided', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'lead' => ['message' => 'no person at all'],
    ])->assertJsonValidationErrors(['person', 'person_id']);
});

it('rejects person_id pointing to a non-existent Person', function () {
    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person_id' => 999999,
        'lead' => ['message' => 'ghost person'],
    ])->assertJsonValidationErrors(['person_id']);
});

it('rejects person_id pointing to a soft-deleted Person', function () {
    $deleted = Person::factory()->create();
    $deleted->delete();

    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person_id' => $deleted->id,
        'lead' => ['message' => 'soft deleted person'],
    ])->assertJsonValidationErrors(['person_id']);
});

it('silently prefers person_id over person when both are provided', function () {
    $existing = Person::factory()->create(['first_name' => 'Existing']);

    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person_id' => $existing->id,
        'person' => ['first_name' => 'Ignored', 'email' => 'ignored@test.com'],
        'lead' => ['message' => 'both provided'],
    ])->assertCreated();

    // Still just one Person — the inline `person` payload was ignored
    expect(Person::count())->toEqual(1);
    expect(Lead::first()->person_id)->toEqual($existing->id);
});

it('ignores company payload when person_id is given', function () {
    $personCompany = Company::factory()->create(['name' => 'PersonCo']);
    $existing = Person::factory()->create(['company_id' => $personCompany->id]);

    $this->actingAs($this->admin)->postJson('/api/leads', [
        'person_id' => $existing->id,
        'company' => ['name' => 'OverrideCo'],
        'lead' => ['message' => 'company override attempt'],
    ])->assertCreated();

    // No new company was created from the payload
    expect(Company::count())->toEqual(1);
    expect(Lead::first()->company_id)->toEqual($personCompany->id);
});
