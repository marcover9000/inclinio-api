<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('belongs to a company (nullable)', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    expect($person->company)->not->toBeNull();
    expect($person->company->id)->toEqual($company->id);

    $particular = Person::factory()->create(['company_id' => null]);
    expect($particular->company)->toBeNull();
});

it('exposes fullName accessor', function () {
    $person = Person::factory()->create(['first_name' => 'Marc', 'last_name' => 'Sanahuja']);
    expect($person->fullName)->toEqual('Marc Sanahuja');

    $onlyFirst = Person::factory()->create(['first_name' => 'Núria', 'last_name' => null]);
    expect($onlyFirst->fullName)->toEqual('Núria');
});

it('promoteToClient is idempotent', function () {
    $person = Person::factory()->create(['is_client' => false]);
    $person->promoteToClient();
    $first = $person->fresh()->became_client_at;

    sleep(1);
    $person->promoteToClient();
    expect($person->fresh()->became_client_at->equalTo($first))->toBeTrue();
});
