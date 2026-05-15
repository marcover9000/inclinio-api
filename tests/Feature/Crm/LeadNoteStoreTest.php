<?php

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Domain\Models\LeadNote;

it('creates a note with author = authenticated user', function () {
    $admin = actingAsAdmin();
    $lead = Lead::factory()->create();
    $this->postJson("/api/leads/{$lead->id}/notes", [
        'body' => 'una nota',
    ])->assertCreated();
    expect(LeadNote::count())->toEqual(1);
    expect(LeadNote::first()->author_id)->toEqual($admin->id);
});

it('requires auth', function () {
    $lead = Lead::factory()->create();
    $this->postJson("/api/leads/{$lead->id}/notes", ['body' => 'x'])->assertUnauthorized();
});

it('requires body', function () {
    actingAsAdmin();
    $lead = Lead::factory()->create();
    $this->postJson("/api/leads/{$lead->id}/notes", [])
        ->assertJsonValidationErrors(['body']);
});
