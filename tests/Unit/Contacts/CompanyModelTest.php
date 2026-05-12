<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('has the expected fillable and cast attributes', function () {
    $company = new Company();
    expect($company->getFillable())->toEqualCanonicalizing([
        'name', 'vat', 'website', 'address', 'notes', 'is_client', 'became_client_at',
    ]);
    expect($company->getCasts())->toMatchArray([
        'is_client' => 'boolean',
        'became_client_at' => 'datetime',
    ]);
});

it('uses soft deletes', function () {
    $company = Company::factory()->create();
    $company->delete();
    expect(Company::find($company->id))->toBeNull();
    expect(Company::withTrashed()->find($company->id))->not->toBeNull();
});

it('promoteToClient sets is_client and became_client_at idempotently', function () {
    $company = Company::factory()->create(['is_client' => false]);
    $company->promoteToClient();
    expect($company->fresh()->is_client)->toBeTrue();
    expect($company->fresh()->became_client_at)->not->toBeNull();

    $first = $company->fresh()->became_client_at;
    sleep(1);
    $company->promoteToClient();
    expect($company->fresh()->became_client_at->equalTo($first))->toBeTrue();
});
