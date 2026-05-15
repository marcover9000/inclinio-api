<?php

use App\Modules\Crm\Application\Actions\TransitionLeadStatus;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Events\LeadConverted;
use App\Modules\Crm\Domain\Exceptions\InvalidLeadTransition;
use App\Modules\Crm\Domain\Models\Lead;
use Illuminate\Support\Facades\Event;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('transitions to a valid next state and updates status_changed_at', function () {
    Event::fake();
    $lead = Lead::factory()->create(['status' => LeadStatus::New]);
    app(TransitionLeadStatus::class)($lead, LeadStatus::Contacted);
    expect($lead->fresh()->status)->toEqual(LeadStatus::Contacted);
});

it('throws InvalidLeadTransition for invalid transition', function () {
    $lead = Lead::factory()->create(['status' => LeadStatus::New]);
    expect(fn () => app(TransitionLeadStatus::class)($lead, LeadStatus::Won))
        ->toThrow(InvalidLeadTransition::class);
});

it('throws when trying to transition from terminal state', function () {
    $lead = Lead::factory()->won()->create();
    expect(fn () => app(TransitionLeadStatus::class)($lead, LeadStatus::Contacted))
        ->toThrow(InvalidLeadTransition::class);
});

it('dispatches LeadConverted when transitioning to won', function () {
    Event::fake([LeadConverted::class]);
    $lead = Lead::factory()->withStatus(LeadStatus::Proposal)->create();
    app(TransitionLeadStatus::class)($lead, LeadStatus::Won);
    Event::assertDispatched(LeadConverted::class, fn ($e) => $e->lead->id === $lead->id);
});

it('does not dispatch LeadConverted for lost', function () {
    Event::fake([LeadConverted::class]);
    $lead = Lead::factory()->withStatus(LeadStatus::Proposal)->create();
    app(TransitionLeadStatus::class)($lead, LeadStatus::Lost);
    Event::assertNotDispatched(LeadConverted::class);
});

it('throws when transitioning to the same status', function () {
    $lead = Lead::factory()->create(['status' => LeadStatus::New]);
    expect(fn () => app(TransitionLeadStatus::class)($lead, LeadStatus::New))
        ->toThrow(InvalidLeadTransition::class);
});
