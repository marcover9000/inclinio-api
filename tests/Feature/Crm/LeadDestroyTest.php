<?php

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('soft-deletes a lead', function () {
    $lead = Lead::factory()->create();
    $this->actingAs($this->admin)->deleteJson("/api/leads/{$lead->id}")->assertNoContent();
    expect(Lead::find($lead->id))->toBeNull();
    expect(Lead::withTrashed()->find($lead->id))->not->toBeNull();
});

it('requires auth', function () {
    $lead = Lead::factory()->create();
    $this->deleteJson("/api/leads/{$lead->id}")->assertUnauthorized();
});
