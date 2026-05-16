<?php

use Illuminate\Support\Facades\Schema;

it('creates the projects table with the spec columns', function () {
    expect(Schema::hasTable('projects'))->toBeTrue();
    expect(Schema::hasColumns('projects', [
        'id', 'name', 'status', 'is_internal',
        'client_company_id', 'client_person_id',
        'shadow_rate_override_cents', 'shadow_rate_override_currency',
        'started_at', 'due_at', 'deleted_at', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

it('creates the hours_packs table with the spec columns', function () {
    expect(Schema::hasTable('hours_packs'))->toBeTrue();
    expect(Schema::hasColumns('hours_packs', [
        'id', 'project_id', 'hours', 'price_cents', 'price_currency',
        'dated_on', 'reason', 'source_lead_id', 'deleted_at', 'created_at', 'updated_at',
    ]))->toBeTrue();
});
