<?php

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('requires authentication', function () {
    $p = Project::factory()->create();
    $this->patchJson("/api/projects/{$p->id}/status", ['status' => 'paused'])->assertUnauthorized();
});

it('applies a valid transition', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Active)->create();
    $this->actingAs($this->admin)
        ->patchJson("/api/projects/{$p->id}/status", ['status' => 'done'])
        ->assertOk()->assertJsonPath('data.status', 'done');
});

it('reopens a done project to active', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Done)->create();
    $this->actingAs($this->admin)
        ->patchJson("/api/projects/{$p->id}/status", ['status' => 'active'])
        ->assertOk()->assertJsonPath('data.status', 'active');
});

it('returns 422 with an informative message on an illegal transition', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Archived)->create();
    $r = $this->actingAs($this->admin)->patchJson("/api/projects/{$p->id}/status", ['status' => 'done']);
    $r->assertUnprocessable();
    expect($r->json('message'))->toContain('Transició');
});

it('rejects an unknown status value', function () {
    $p = Project::factory()->create();
    $this->actingAs($this->admin)
        ->patchJson("/api/projects/{$p->id}/status", ['status' => 'bogus'])
        ->assertJsonValidationErrors(['status']);
});
