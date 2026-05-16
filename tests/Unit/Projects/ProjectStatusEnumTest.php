<?php

use App\Modules\Projects\Domain\Enums\ProjectStatus;

it('defines the exact lifecycle graph per spec section 6', function () {
    // active ↔ paused · active|paused → done · done → archived
    // done → active (reobrir) · archived → active (reobrir)
    expect(ProjectStatus::Active->canTransitionTo(ProjectStatus::Paused))->toBeTrue();
    expect(ProjectStatus::Active->canTransitionTo(ProjectStatus::Done))->toBeTrue();
    expect(ProjectStatus::Active->canTransitionTo(ProjectStatus::Archived))->toBeFalse();

    expect(ProjectStatus::Paused->canTransitionTo(ProjectStatus::Active))->toBeTrue();
    expect(ProjectStatus::Paused->canTransitionTo(ProjectStatus::Done))->toBeTrue();
    expect(ProjectStatus::Paused->canTransitionTo(ProjectStatus::Archived))->toBeFalse();

    expect(ProjectStatus::Done->canTransitionTo(ProjectStatus::Archived))->toBeTrue();
    expect(ProjectStatus::Done->canTransitionTo(ProjectStatus::Active))->toBeTrue(); // reobre
    expect(ProjectStatus::Done->canTransitionTo(ProjectStatus::Paused))->toBeFalse();

    expect(ProjectStatus::Archived->canTransitionTo(ProjectStatus::Active))->toBeTrue(); // reobre
    expect(ProjectStatus::Archived->canTransitionTo(ProjectStatus::Done))->toBeFalse();

    // Mai a si mateix (cap estat)
    foreach (ProjectStatus::cases() as $s) {
        expect($s->canTransitionTo($s))->toBeFalse("Self-transition ha de ser falsa per {$s->value}");
    }
});

it('exposes a Catalan label for every case', function () {
    foreach (ProjectStatus::cases() as $s) {
        expect($s->label())->toBeString()->not->toBe('');
    }
});
