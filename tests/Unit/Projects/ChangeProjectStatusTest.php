<?php

use App\Modules\Projects\Application\Actions\ChangeProjectStatus;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Exceptions\InvalidProjectTransition;
use App\Modules\Projects\Domain\Models\Project;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('applies a valid transition', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Active)->create();
    app(ChangeProjectStatus::class)($p, ProjectStatus::Paused);
    expect($p->fresh()->status)->toEqual(ProjectStatus::Paused);
});

it('reopens a done project to active', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Done)->create();
    app(ChangeProjectStatus::class)($p, ProjectStatus::Active);
    expect($p->fresh()->status)->toEqual(ProjectStatus::Active);
});

it('throws InvalidProjectTransition on an illegal jump', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Archived)->create();
    expect(fn () => app(ChangeProjectStatus::class)($p, ProjectStatus::Done))
        ->toThrow(InvalidProjectTransition::class);
});

it('throws when transitioning to the same status', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Active)->create();
    expect(fn () => app(ChangeProjectStatus::class)($p, ProjectStatus::Active))
        ->toThrow(InvalidProjectTransition::class);
});
