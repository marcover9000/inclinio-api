<?php

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Exceptions\InvalidProjectTransition;
use App\Modules\Projects\Domain\Exceptions\LeadNotConvertible;

it('InvalidProjectTransition carries an informative message', function () {
    $e = new InvalidProjectTransition(ProjectStatus::Archived, ProjectStatus::Done);
    expect($e)->toBeInstanceOf(RuntimeException::class)
        ->and($e->getMessage())->toContain('archived')
        ->and($e->getMessage())->toContain('done');
});

it('LeadNotConvertible names the offending lead status', function () {
    $e = new LeadNotConvertible(LeadStatus::New);
    expect($e)->toBeInstanceOf(RuntimeException::class)
        ->and($e->getMessage())->toContain('new');
});
