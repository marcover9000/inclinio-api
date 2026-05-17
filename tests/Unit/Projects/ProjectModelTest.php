<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('casts status/is_internal/dates and soft-deletes', function () {
    $p = Project::factory()->create([
        'status' => ProjectStatus::Active,
        'is_internal' => true,
    ]);
    $fresh = $p->fresh();
    expect($fresh->status)->toEqual(ProjectStatus::Active)
        ->and($fresh->is_internal)->toBeTrue();

    $p->delete();
    expect(Project::count())->toBe(0)
        ->and(Project::withTrashed()->count())->toBe(1);
});

it('links to a client company or person', function () {
    $company = Company::factory()->create();
    $person = Person::factory()->create();

    $pc = Project::factory()->forCompany($company)->create();
    $pp = Project::factory()->forPerson($person)->create();

    expect($pc->clientCompany)->toBeInstanceOf(Company::class)
        ->and($pc->clientCompany->id)->toBe($company->id)
        ->and($pp->clientPerson)->toBeInstanceOf(Person::class)
        ->and($pp->clientPerson->id)->toBe($person->id);
});

it('casts a shadow_rate_override Money override (nullable)', function () {
    $with = Project::factory()->create([
        'shadow_rate_override' => Money::fromCents(4500, 'EUR'),
    ]);
    $without = Project::factory()->create();

    expect($with->fresh()->shadow_rate_override)->toBeInstanceOf(Money::class)
        ->and($with->fresh()->shadow_rate_override->amountCents)->toBe(4500)
        ->and($without->fresh()->shadow_rate_override)->toBeNull();
});

it('scopes: ofStatus, internal, forClientCompany, search', function () {
    $company = Company::factory()->create();
    Project::factory()->forCompany($company)->create(['name' => 'Estadia API']);
    Project::factory()->internal()->create(['name' => 'Perruqueries']);
    Project::factory()->withStatus(ProjectStatus::Done)->create(['name' => 'Antic']);

    expect(Project::ofStatus([ProjectStatus::Done->value])->count())->toBe(1)
        ->and(Project::internal()->count())->toBe(1)
        ->and(Project::forClientCompany($company->id)->count())->toBe(1)
        ->and(Project::search('estadia')->count())->toBe(1);
});
