<?php

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Domain\Models\LeadNote;
use App\Modules\Identity\Domain\Models\User;

it('creates a note with author = authenticated user', function () {
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create();
    $this->actingAs($admin)->postJson("/api/leads/{$lead->id}/notes", [
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
    $admin = User::factory()->admin()->create();
    $lead = Lead::factory()->create();
    $this->actingAs($admin)->postJson("/api/leads/{$lead->id}/notes", [])
        ->assertJsonValidationErrors(['body']);
});
