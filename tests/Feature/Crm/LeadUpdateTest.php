<?php

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('updates message and tags', function () {
    $lead = Lead::factory()->create();
    $this->actingAs($this->admin)->patchJson("/api/leads/{$lead->id}", [
        'message' => 'updated message body',
        'tags' => ['updated-tag'],
    ])->assertOk();
    expect($lead->fresh()->message)->toEqual('updated message body');
});

it('does not allow updating status via patch', function () {
    $lead = Lead::factory()->create(['status' => LeadStatus::New]);
    $this->actingAs($this->admin)->patchJson("/api/leads/{$lead->id}", [
        'message' => 'something long enough',
        'status' => 'won',
    ])->assertOk();
    expect($lead->fresh()->status)->toEqual(LeadStatus::New);
});

it('validates tags max 10', function () {
    $lead = Lead::factory()->create();
    $this->actingAs($this->admin)->patchJson("/api/leads/{$lead->id}", [
        'tags' => array_fill(0, 11, 'x'),
    ])->assertJsonValidationErrors(['tags']);
});
