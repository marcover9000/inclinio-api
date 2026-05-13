<?php

use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
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

it('blocks delete when person has active leads', function () {
    $person = Person::factory()->create();
    Lead::factory()->create([
        'person_id' => $person->id,
        'status' => LeadStatus::New,
    ]);

    $resp = $this->actingAs($this->admin)->deleteJson("/api/people/{$person->id}");
    $resp->assertStatus(422)
        ->assertJsonPath('message', "Aquesta persona té leads actius. Tanca'ls com a Guanyat o Perdut abans d'eliminar-la.");

    expect(Person::find($person->id))->not->toBeNull();
});

it('allows delete when all person leads are terminal', function () {
    $person = Person::factory()->create();
    Lead::factory()->won()->create(['person_id' => $person->id]);
    Lead::factory()->lost()->create(['person_id' => $person->id]);

    $this->actingAs($this->admin)->deleteJson("/api/people/{$person->id}")->assertNoContent();
    expect(Person::find($person->id))->toBeNull();
});

it('returns leads on show', function () {
    $person = Person::factory()->create();
    Lead::factory()->count(2)->create(['person_id' => $person->id]);

    $resp = $this->actingAs($this->admin)->getJson("/api/people/{$person->id}")->assertOk();
    $resp->assertJsonStructure(['data' => ['id', 'leads' => [['id', 'status']]]]);
    expect(count($resp->json('data.leads')))->toEqual(2);
});
