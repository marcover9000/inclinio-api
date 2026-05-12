<?php

use App\Modules\Contacts\Application\Actions\MarkPersonAsClient;
use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;

it('marks a person without company as client', function () {
    $person = Person::factory()->create(['company_id' => null, 'is_client' => false]);
    app(MarkPersonAsClient::class)($person);
    expect($person->fresh()->is_client)->toBeTrue();
    expect($person->fresh()->became_client_at)->not->toBeNull();
});

it('marks both person and company as client', function () {
    $company = Company::factory()->create(['is_client' => false]);
    $person = Person::factory()->create(['company_id' => $company->id, 'is_client' => false]);
    app(MarkPersonAsClient::class)($person);
    expect($person->fresh()->is_client)->toBeTrue();
    expect($company->fresh()->is_client)->toBeTrue();
});

it('is idempotent — does not overwrite became_client_at on re-invocation', function () {
    $person = Person::factory()->create();
    $action = app(MarkPersonAsClient::class);
    $action($person);
    $first = $person->fresh()->became_client_at;

    sleep(1);
    $action($person);
    expect($person->fresh()->became_client_at->equalTo($first))->toBeTrue();
});

it('marks only company if person is already client but company is not', function () {
    $company = Company::factory()->create(['is_client' => false]);
    $person = Person::factory()->client()->create(['company_id' => $company->id]);
    app(MarkPersonAsClient::class)($person);
    expect($company->fresh()->is_client)->toBeTrue();
});
