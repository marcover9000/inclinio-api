<?php

use App\Modules\Crm\Domain\Enums\LeadStatus;

it('defines the exact transition graph per spec section 6.2', function () {
    expect(LeadStatus::New->canTransitionTo(LeadStatus::Contacted))->toBeTrue();
    expect(LeadStatus::New->canTransitionTo(LeadStatus::Lost))->toBeTrue();
    expect(LeadStatus::New->canTransitionTo(LeadStatus::Qualified))->toBeFalse();
    expect(LeadStatus::New->canTransitionTo(LeadStatus::Won))->toBeFalse();

    expect(LeadStatus::Contacted->canTransitionTo(LeadStatus::Qualified))->toBeTrue();
    expect(LeadStatus::Contacted->canTransitionTo(LeadStatus::Lost))->toBeTrue();
    expect(LeadStatus::Contacted->canTransitionTo(LeadStatus::New))->toBeFalse();

    expect(LeadStatus::Qualified->canTransitionTo(LeadStatus::Proposal))->toBeTrue();
    expect(LeadStatus::Qualified->canTransitionTo(LeadStatus::Lost))->toBeTrue();

    expect(LeadStatus::Proposal->canTransitionTo(LeadStatus::Won))->toBeTrue();
    expect(LeadStatus::Proposal->canTransitionTo(LeadStatus::Lost))->toBeTrue();

    // Terminals
    expect(LeadStatus::Won->canTransitionTo(LeadStatus::Lost))->toBeFalse();
    expect(LeadStatus::Lost->canTransitionTo(LeadStatus::New))->toBeFalse();

    // Mai a si mateix
    expect(LeadStatus::New->canTransitionTo(LeadStatus::New))->toBeFalse();
});
