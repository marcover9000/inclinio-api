<?php

use App\Modules\Contacts\Application\Actions\CreateOrFindCompany;
use App\Modules\Contacts\Domain\Models\Company;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates a new company when name does not exist', function () {
    $action = app(CreateOrFindCompany::class);
    $company = $action(['name' => 'Acme S.L.', 'vat' => 'B12345678']);
    expect(Company::count())->toEqual(1);
    expect($company->name)->toEqual('Acme S.L.');
    expect($company->vat)->toEqual('B12345678');
});

it('finds an existing company by case-insensitive name match', function () {
    Company::factory()->create(['name' => 'Acme S.L.']);
    $action = app(CreateOrFindCompany::class);
    $found = $action(['name' => 'ACME s.l.']);
    expect(Company::count())->toEqual(1);
    expect($found->name)->toEqual('Acme S.L.');
});

it('updates null fields on existing company when new data provided', function () {
    Company::factory()->create(['name' => 'Acme', 'vat' => null, 'website' => null]);
    $action = app(CreateOrFindCompany::class);
    $action(['name' => 'acme', 'vat' => 'B999', 'website' => 'https://acme.test']);
    $company = Company::first();
    expect($company->vat)->toEqual('B999');
    expect($company->website)->toEqual('https://acme.test');
});

it('does not overwrite existing non-null fields', function () {
    Company::factory()->create(['name' => 'Acme', 'vat' => 'B111']);
    $action = app(CreateOrFindCompany::class);
    $action(['name' => 'acme', 'vat' => 'B999']);
    expect(Company::first()->vat)->toEqual('B111');
});
