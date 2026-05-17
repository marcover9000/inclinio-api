<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Http\Resources\ProjectResource;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('serializes a project with money aggregates and loaded client', function () {
    $company = Company::factory()->create(['name' => 'ACME']);
    $project = Project::factory()->forCompany($company)->create([
        'name' => 'Estadia API',
        'shadow_rate_override' => Money::fromCents(4500, 'EUR'),
    ]);
    HoursPack::factory()->create([
        'project_id' => $project->id, 'hours' => 40, 'price' => Money::fromCents(400000, 'EUR'),
    ]);

    $project->load(['clientCompany', 'clientPerson', 'hoursPacks']);
    $arr = (new ProjectResource($project))->toArray(request());

    expect($arr['id'])->toBe($project->id)
        ->and($arr['name'])->toBe('Estadia API')
        ->and($arr['status'])->toBe('active')
        ->and($arr['status_label'])->toBe('Actiu')
        ->and($arr['is_internal'])->toBeFalse()
        ->and($arr['total_price'])->toBe(['cents' => 400000, 'currency' => 'EUR', 'formatted' => '4.000,00 EUR'])
        ->and($arr['budgeted_hours'])->toBe(40)
        ->and($arr['shadow_rate_override']['cents'])->toBe(4500)
        ->and($arr['client_company']['name'])->toBe('ACME')
        ->and($arr['hours_packs'])->toHaveCount(1)
        ->and($arr['hours_packs'][0]['price']['cents'])->toBe(400000);
});

it('serializes shadow_rate_override as null when absent', function () {
    $project = Project::factory()->create();
    $project->load(['clientCompany', 'clientPerson', 'hoursPacks']);
    expect((new ProjectResource($project))->toArray(request())['shadow_rate_override'])->toBeNull();
});
