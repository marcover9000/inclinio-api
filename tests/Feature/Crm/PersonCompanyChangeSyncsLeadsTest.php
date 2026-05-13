<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;

it('updates company_id on active leads when person.company_id changes', function () {
    $oldCompany = Company::factory()->create();
    $newCompany = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $oldCompany->id]);
    $activeLead = Lead::factory()->create([
        'person_id' => $person->id,
        'company_id' => $oldCompany->id,
        'status' => LeadStatus::New,
    ]);
    $wonLead = Lead::factory()->create([
        'person_id' => $person->id,
        'company_id' => $oldCompany->id,
        'status' => LeadStatus::Won,
    ]);

    $person->update(['company_id' => $newCompany->id]);

    expect($activeLead->fresh()->company_id)->toEqual($newCompany->id);
    expect($wonLead->fresh()->company_id)->toEqual($oldCompany->id);
});

it('does not touch leads when person.company_id is unchanged', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->create(['person_id' => $person->id, 'company_id' => $company->id]);
    $person->update(['first_name' => 'New Name']);
    expect($lead->fresh()->company_id)->toEqual($company->id);
});

it('sets company_id to null on active leads when person becomes a particular', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create(['company_id' => $company->id]);
    $lead = Lead::factory()->create([
        'person_id' => $person->id,
        'company_id' => $company->id,
        'status' => LeadStatus::Contacted,
    ]);
    $person->update(['company_id' => null]);
    expect($lead->fresh()->company_id)->toBeNull();
});
