<?php

use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = actingAsAdmin();
});

it('requires authentication', function () {
    $this->app['auth']->forgetGuards();
    $p = Project::factory()->create();
    $this->postJson("/api/projects/{$p->id}/tasks", [])->assertUnauthorized();
});

it('creates, updates and soft-deletes a task scoped to the project', function () {
    $p = Project::factory()->create();

    $this->postJson("/api/projects/{$p->id}/tasks", [
        'title' => 'Disseny', 'status' => 'doing', 'estimate_hours' => 4,
    ])->assertCreated()->assertJsonPath('data.tasks.0.title', 'Disseny');

    $task = Task::firstOrFail();
    $this->patchJson("/api/projects/{$p->id}/tasks/{$task->id}", [
        'title' => 'Disseny v2', 'status' => 'done',
    ])->assertOk()->assertJsonPath('data.tasks.0.status', 'done');

    $this->deleteJson("/api/projects/{$p->id}/tasks/{$task->id}")->assertNoContent();
    expect(Task::find($task->id))->toBeNull()
        ->and(Task::withTrashed()->find($task->id))->not->toBeNull();
});

it('404s when task does not belong to the project', function () {
    $a = Project::factory()->create();
    $b = Project::factory()->create();
    $task = Task::factory()->create(['project_id' => $b->id]);

    $this->patchJson("/api/projects/{$a->id}/tasks/{$task->id}", ['title' => 'x'])->assertNotFound();
});

it('validates title required', function () {
    $p = Project::factory()->create();
    $this->postJson("/api/projects/{$p->id}/tasks", ['title' => ''])
        ->assertStatus(422)->assertJsonValidationErrors(['title']);
});
