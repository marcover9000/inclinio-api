<?php

use App\Modules\Contacts\Domain\Models\Company;
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
