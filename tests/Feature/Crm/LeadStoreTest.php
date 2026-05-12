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
