<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Events\LeadConverted;
use App\Modules\Crm\Domain\Models\Lead;

it('marks person as client when lead is converted', function () {
    $person = Person::factory()->create(['is_client' => false]);
    $lead = Lead::factory()->create(['person_id' => $person->id, 'status' => LeadStatus::Won]);
    LeadConverted::dispatch($lead);
    expect($person->fresh()->is_client)->toBeTrue();
});

it('marks company as client when lead is converted', function () {
    $company = Company::factory()->create(['is_client' => false]);
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->create(['person_id' => $person->id, 'company_id' => $company->id]);
    LeadConverted::dispatch($lead);
    expect($company->fresh()->is_client)->toBeTrue();
});

it('handles person without company gracefully', function () {
    $person = Person::factory()->create(['company_id' => null]);
    $lead = Lead::factory()->create(['person_id' => $person->id, 'company_id' => null]);
    LeadConverted::dispatch($lead);
    expect($person->fresh()->is_client)->toBeTrue();
});

it('is idempotent', function () {
    $person = Person::factory()->client()->create();
    $first = $person->became_client_at;
    $lead = Lead::factory()->create(['person_id' => $person->id]);
    sleep(1);
    LeadConverted::dispatch($lead);
    expect($person->fresh()->became_client_at->equalTo($first))->toBeTrue();
});

it('only marks company when company is not already a client', function () {
    $company = Company::factory()->client()->create();
    $first = $company->became_client_at;
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->create(['person_id' => $person->id, 'company_id' => $company->id]);
    sleep(1);
    LeadConverted::dispatch($lead);
    expect($company->fresh()->became_client_at->equalTo($first))->toBeTrue();
});
