<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;

it('creates a lead from public form with company', function () {
    $resp = $this->postJson('/api/public/leads', [
        'first_name' => 'Marc',
        'email' => 'marc@test.com',
        'company_name' => 'Acme',
        'message' => 'Vol web nova',
    ]);
    $resp->assertCreated()->assertJson(['message' => 'received']);
    expect(Lead::count())->toEqual(1);
    expect(Lead::first()->source)->toEqual(LeadSource::WebForm);
    expect(Lead::first()->status)->toEqual(LeadStatus::New);
    expect(Person::count())->toEqual(1);
    expect(Company::count())->toEqual(1);
});

it('creates a lead without company', function () {
    $this->postJson('/api/public/leads', [
        'first_name' => 'Marc',
        'email' => 'marc@test.com',
        'message' => 'Hola hola hola',
    ])->assertCreated();
    expect(Company::count())->toEqual(0);
});

it('returns no id in response (defensive)', function () {
    $resp = $this->postJson('/api/public/leads', [
        'first_name' => 'Marc',
        'email' => 'marc@test.com',
        'message' => 'Hola hola',
    ]);
    $resp->assertCreated();
    expect($resp->json())->toEqual(['message' => 'received']);
});

it('silently ignores honeypot submissions', function () {
    $resp = $this->postJson('/api/public/leads', [
        'first_name' => 'Bot',
        'email' => 'bot@spam.com',
        'message' => 'spam',
        '_hp' => 'caught',
    ]);
    $resp->assertCreated()->assertJson(['message' => 'received']);
    expect(Lead::count())->toEqual(0);
});

it('reuses existing company by case-insensitive name', function () {
    Company::factory()->create(['name' => 'Acme S.L.']);
    $this->postJson('/api/public/leads', [
        'first_name' => 'Marc',
        'email' => 'marc@test.com',
        'company_name' => 'acme s.l.',
        'message' => 'hola',
    ])->assertCreated();
    expect(Company::count())->toEqual(1);
});

it('validates required fields', function () {
    $this->postJson('/api/public/leads', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'email', 'message']);
});

it('validates email format', function () {
    $this->postJson('/api/public/leads', [
        'first_name' => 'X',
        'email' => 'not-an-email',
        'message' => 'hola hola',
    ])->assertJsonValidationErrors(['email']);
});

it('validates message minimum length', function () {
    $this->postJson('/api/public/leads', [
        'first_name' => 'X',
        'email' => 'x@x.com',
        'message' => 'hi',
    ])->assertJsonValidationErrors(['message']);
});
