<?php

use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('lists people paginated', function () {
    Person::factory()->count(15)->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/people');
    $resp->assertOk()->assertJsonStructure(['data', 'meta']);
});

it('filters by is_client', function () {
    Person::factory()->count(3)->create();
    Person::factory()->client()->count(2)->create();
    $resp = $this->actingAs($this->admin)->getJson('/api/people?is_client=1');
    expect(count($resp->json('data')))->toEqual(2);
});

it('shows a person with company and leads', function () {
    $person = Person::factory()->create();
    $this->actingAs($this->admin)->getJson("/api/people/{$person->id}")
        ->assertOk()->assertJsonStructure(['data' => ['id', 'first_name', 'company']]);
});

it('updates a person', function () {
    $person = Person::factory()->create();
    $this->actingAs($this->admin)->patchJson("/api/people/{$person->id}", [
        'first_name' => 'Updated',
    ])->assertOk();
    expect($person->fresh()->first_name)->toEqual('Updated');
});

it('soft-deletes a person', function () {
    $person = Person::factory()->create();
    $this->actingAs($this->admin)->deleteJson("/api/people/{$person->id}")->assertNoContent();
    expect(Person::find($person->id))->toBeNull();
});

it('requires auth', function () {
    $this->getJson('/api/people')->assertUnauthorized();
});
