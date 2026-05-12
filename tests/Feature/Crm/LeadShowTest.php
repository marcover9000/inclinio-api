<?php

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Domain\Models\LeadNote;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('returns lead with person, company, and notes', function () {
    $lead = Lead::factory()->create();
    LeadNote::factory()->count(2)->create(['lead_id' => $lead->id]);
    $resp = $this->actingAs($this->admin)->getJson("/api/leads/{$lead->id}");
    $resp->assertOk()->assertJsonStructure([
        'data' => ['id', 'person' => ['id'], 'notes' => [['id', 'body']]],
    ]);
});

it('404 if not found', function () {
    $this->actingAs($this->admin)->getJson('/api/leads/999')->assertNotFound();
});

it('requires auth', function () {
    $lead = Lead::factory()->create();
    $this->getJson("/api/leads/{$lead->id}")->assertUnauthorized();
});
