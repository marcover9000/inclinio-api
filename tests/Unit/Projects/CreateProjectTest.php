<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Projects\Application\Actions\CreateProject;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('creates an active project for a client company, no pack', function () {
    $company = Company::factory()->create();
    $project = app(CreateProject::class)([
        'name' => 'Estadia API',
        'client_company_id' => $company->id,
    ]);

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->status)->toEqual(ProjectStatus::Active)
        ->and($project->is_internal)->toBeFalse()
        ->and($project->client_company_id)->toBe($company->id)
        ->and($project->hoursPacks()->count())->toBe(0);
});

it('creates an internal project without client', function () {
    $project = app(CreateProject::class)([
        'name' => 'Perruqueries (especulatiu)',
        'is_internal' => true,
    ]);

    expect($project->is_internal)->toBeTrue()
        ->and($project->client_company_id)->toBeNull()
        ->and($project->client_person_id)->toBeNull();
});

it('creates a project with pack #1 (initial sale)', function () {
    $company = Company::factory()->create();
    $project = app(CreateProject::class)([
        'name' => 'Web client',
        'client_company_id' => $company->id,
        'pack' => [
            'hours' => 50,
            'price' => Money::fromCents(600000, 'EUR'),
            'reason' => 'Venda inicial',
        ],
    ]);

    $pack = $project->hoursPacks()->first();
    expect($project->hoursPacks()->count())->toBe(1)
        ->and($pack->hours)->toBe(50)
        ->and($pack->price->amountCents)->toBe(600000)
        ->and($pack->dated_on->toDateString())->toBe(now()->toDateString());
});
