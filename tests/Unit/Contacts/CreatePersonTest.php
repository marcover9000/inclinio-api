<?php

use App\Modules\Contacts\Application\Actions\CreatePerson;
use App\Modules\Contacts\Domain\Models\Person;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a person without company', function () {
    $action = app(CreatePerson::class);
    $person = $action(['first_name' => 'Marc', 'email' => 'marc@test.com']);
    expect($person->first_name)->toEqual('Marc');
    expect($person->company_id)->toBeNull();
});

it('throws when email already exists on a non-trashed person', function () {
    Person::factory()->create(['email' => 'dup@test.com']);
    $action = app(CreatePerson::class);
    expect(fn () => $action(['first_name' => 'X', 'email' => 'dup@test.com']))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('allows reusing email of soft-deleted person', function () {
    $deleted = Person::factory()->create(['email' => 'old@test.com']);
    $deleted->delete();
    $action = app(CreatePerson::class);
    $new = $action(['first_name' => 'New', 'email' => 'old@test.com']);
    expect($new->id)->not->toEqual($deleted->id);
});
