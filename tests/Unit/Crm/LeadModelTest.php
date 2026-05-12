<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('belongs to person and optionally company', function () {
    $person = Person::factory()->create();
    $lead = Lead::factory()->create(['person_id' => $person->id, 'company_id' => null]);
    expect($lead->person->id)->toEqual($person->id);
    expect($lead->company)->toBeNull();
});

it('casts status, source, tags', function () {
    $lead = Lead::factory()->create(['status' => 'new', 'source' => 'manual', 'tags' => ['web']]);
    expect($lead->status)->toBeInstanceOf(LeadStatus::class);
    expect($lead->source)->toBeInstanceOf(LeadSource::class);
    expect($lead->tags)->toEqual(['web']);
});

it('scopeActive excludes won and lost', function () {
    Lead::factory()->create(['status' => 'new']);
    Lead::factory()->create(['status' => 'won']);
    Lead::factory()->create(['status' => 'lost']);
    expect(Lead::active()->count())->toEqual(1);
});

it('uses soft deletes', function () {
    $lead = Lead::factory()->create();
    $lead->delete();
    expect(Lead::find($lead->id))->toBeNull();
    expect(Lead::withTrashed()->find($lead->id))->not->toBeNull();
});
