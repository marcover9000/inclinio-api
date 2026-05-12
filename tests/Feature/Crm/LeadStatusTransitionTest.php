<?php

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

dataset('validTransitions', [
    [LeadStatus::New, LeadStatus::Contacted],
    [LeadStatus::New, LeadStatus::Lost],
    [LeadStatus::Contacted, LeadStatus::Qualified],
    [LeadStatus::Contacted, LeadStatus::Lost],
    [LeadStatus::Qualified, LeadStatus::Proposal],
    [LeadStatus::Qualified, LeadStatus::Lost],
    [LeadStatus::Proposal, LeadStatus::Won],
    [LeadStatus::Proposal, LeadStatus::Lost],
]);

dataset('invalidTransitions', [
    [LeadStatus::New, LeadStatus::Won],
    [LeadStatus::New, LeadStatus::Qualified],
    [LeadStatus::Contacted, LeadStatus::Won],
    [LeadStatus::Won, LeadStatus::Lost],
    [LeadStatus::Lost, LeadStatus::New],
]);

it('allows valid transition', function (LeadStatus $from, LeadStatus $to) {
    $lead = Lead::factory()->withStatus($from)->create();
    $this->actingAs($this->admin)->patchJson("/api/leads/{$lead->id}/status", [
        'status' => $to->value,
    ])->assertOk();
    expect($lead->fresh()->status)->toEqual($to);
})->with('validTransitions');

it('rejects invalid transition', function (LeadStatus $from, LeadStatus $to) {
    $lead = Lead::factory()->withStatus($from)->create();
    $this->actingAs($this->admin)->patchJson("/api/leads/{$lead->id}/status", [
        'status' => $to->value,
    ])->assertUnprocessable();
})->with('invalidTransitions');

it('returns 422 with informative message on invalid transition', function () {
    $lead = Lead::factory()->withStatus(LeadStatus::New)->create();
    $resp = $this->actingAs($this->admin)->patchJson("/api/leads/{$lead->id}/status", [
        'status' => 'won',
    ]);
    $resp->assertUnprocessable();
    expect($resp->json('message'))->toContain('Transició');
});
