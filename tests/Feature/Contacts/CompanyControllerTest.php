<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists companies paginated', function () {
    Company::factory()->count(12)->create();
    $this->actingAs($this->admin)->getJson('/api/companies')
        ->assertOk()->assertJsonStructure(['data', 'meta']);
});

it('filters by is_client', function () {
    Company::factory()->count(2)->create();
    Company::factory()->client()->count(3)->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/companies?is_client=1');
    expect(count($resp->json('data')))->toEqual(3);
});

it('searches by name', function () {
    Company::factory()->create(['name' => 'Acme S.L.']);
    Company::factory()->create(['name' => 'Globex']);
    $resp = $this->actingAs($this->admin)->getJson('/api/companies?search=acme');
    expect(count($resp->json('data')))->toEqual(1);
});

it('shows a company', function () {
    $c = Company::factory()->create();
    $this->actingAs($this->admin)->getJson("/api/companies/{$c->id}")->assertOk();
});

it('updates a company', function () {
    $c = Company::factory()->create();
    $this->actingAs($this->admin)->patchJson("/api/companies/{$c->id}", ['notes' => 'updated'])->assertOk();
    expect($c->fresh()->notes)->toEqual('updated');
});

it('soft-deletes a company', function () {
    $c = Company::factory()->create();
    $this->actingAs($this->admin)->deleteJson("/api/companies/{$c->id}")->assertNoContent();
});

it('rejects PATCH with a name that exists on another live company', function () {
    Company::factory()->create(['name' => 'Acme S.L.']);
    $other = Company::factory()->create(['name' => 'Globex']);
    $this->actingAs($this->admin)
        ->patchJson("/api/companies/{$other->id}", ['name' => 'Acme S.L.'])
        ->assertUnprocessable();
});

it('allows PATCH to a name that only exists on a soft-deleted company', function () {
    $deleted = Company::factory()->create(['name' => 'Ghost Inc']);
    $deleted->delete();
    $live = Company::factory()->create(['name' => 'Globex']);
    $this->actingAs($this->admin)
        ->patchJson("/api/companies/{$live->id}", ['name' => 'Ghost Inc'])
        ->assertOk();
});

it('allows PATCH that keeps the same name (no-op)', function () {
    $c = Company::factory()->create(['name' => 'Acme']);
    $this->actingAs($this->admin)
        ->patchJson("/api/companies/{$c->id}", ['name' => 'Acme', 'notes' => 'updated'])
        ->assertOk();
});

it('returns people and leads on show', function () {
    $company = Company::factory()->create(['name' => 'Acme']);
    $personA = Person::factory()->create(['company_id' => $company->id, 'first_name' => 'Alice']);
    $personB = Person::factory()->create(['company_id' => $company->id, 'first_name' => 'Bob']);
    Lead::factory()->create(['person_id' => $personA->id, 'company_id' => $company->id]);
    Lead::factory()->create(['person_id' => $personB->id, 'company_id' => $company->id]);

    $resp = $this->actingAs($this->admin)->getJson("/api/companies/{$company->id}")->assertOk();
    $resp->assertJsonStructure(['data' => [
        'id',
        'name',
        'people' => [['id', 'first_name']],
        'leads' => [['id', 'status']],
    ]]);
    expect(count($resp->json('data.people')))->toEqual(2);
    expect(count($resp->json('data.leads')))->toEqual(2);
});
