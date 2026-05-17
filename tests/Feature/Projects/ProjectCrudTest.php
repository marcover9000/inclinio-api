<?php

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('requires authentication', function () {
    $this->getJson('/api/projects')->assertUnauthorized();
});

it('lists paginated projects with filters', function () {
    $company = Company::factory()->create();
    Project::factory()->forCompany($company)->create(['name' => 'Estadia API']);
    Project::factory()->internal()->create(['name' => 'Perruqueries']);
    Project::factory()->withStatus(ProjectStatus::Done)->create(['name' => 'Antic']);

    $r = $this->actingAs($this->admin)->getJson('/api/projects');
    $r->assertOk()->assertJsonStructure(['data', 'meta' => ['current_page', 'total']]);
    expect(count($r->json('data')))->toBe(3);

    expect(count($this->actingAs($this->admin)->getJson('/api/projects?status=done')->json('data')))->toBe(1);
    expect(count($this->actingAs($this->admin)->getJson('/api/projects?is_internal=1')->json('data')))->toBe(1);
    expect(count($this->actingAs($this->admin)->getJson("/api/projects?client_company_id={$company->id}")->json('data')))->toBe(1);
    expect(count($this->actingAs($this->admin)->getJson('/api/projects?search=estadia')->json('data')))->toBe(1);
});

it('creates a standalone client project with pack #1', function () {
    $company = Company::factory()->create();
    $r = $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'Web client',
        'is_internal' => false,
        'client_company_id' => $company->id,
        'pack' => ['hours' => 50, 'price_cents' => 600000, 'currency' => 'EUR', 'reason' => 'Venda inicial'],
    ]);
    $r->assertCreated();
    expect($r->json('data.status'))->toBe('active')
        ->and($r->json('data.budgeted_hours'))->toBe(50)
        ->and($r->json('data.total_price.cents'))->toBe(600000);
});

it('creates an internal project without client', function () {
    $r = $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'Especulatiu', 'is_internal' => true,
    ]);
    $r->assertCreated();
    expect($r->json('data.is_internal'))->toBeTrue();
});

it('rejects a non-internal project without a client', function () {
    $this->actingAs($this->admin)->postJson('/api/projects', [
        'name' => 'Sense client', 'is_internal' => false,
    ])->assertJsonValidationErrors(['client_company_id']);
});

it('shows, updates and soft-deletes a project', function () {
    $project = Project::factory()->create(['name' => 'Old']);

    $this->actingAs($this->admin)->getJson("/api/projects/{$project->id}")
        ->assertOk()->assertJsonPath('data.name', 'Old');

    $this->actingAs($this->admin)->patchJson("/api/projects/{$project->id}", ['name' => 'New'])
        ->assertOk()->assertJsonPath('data.name', 'New');

    $this->actingAs($this->admin)->deleteJson("/api/projects/{$project->id}")->assertNoContent();
    expect(Project::count())->toBe(0)->and(Project::withTrashed()->count())->toBe(1);
});
