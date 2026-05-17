<?php

use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;
use App\Modules\Projects\Domain\Models\TimeEntry;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->admin = actingAsAdmin();
});

it('creates a time entry assigned to the auth user, no money fields', function () {
    $p = Project::factory()->create();

    $res = $this->postJson("/api/projects/{$p->id}/time-entries", [
        'worked_on' => now()->toDateString(), 'minutes' => 90, 'description' => 'Feina',
    ])->assertCreated();

    $te = TimeEntry::firstOrFail();
    expect($te->user_id)->toBe($this->admin->id)->and($te->minutes)->toBe(90);
    // mai diners: el recurs no exposa cap clau money
    $res->assertJsonMissingPath('data.time_entries.0.price');
});

it('rejects minutes not multiple of 15 and <= 0', function () {
    $p = Project::factory()->create();
    $this->postJson("/api/projects/{$p->id}/time-entries", [
        'worked_on' => now()->toDateString(), 'minutes' => 20, 'description' => 'x',
    ])->assertStatus(422)->assertJsonValidationErrors(['minutes']);
    $this->postJson("/api/projects/{$p->id}/time-entries", [
        'worked_on' => now()->toDateString(), 'minutes' => 0, 'description' => 'x',
    ])->assertStatus(422)->assertJsonValidationErrors(['minutes']);
});

it('updates, soft-deletes, scopes to project, validates task belongs to project', function () {
    $p = Project::factory()->create();
    $te = TimeEntry::factory()->create(['project_id' => $p->id, 'user_id' => $this->admin->id]);

    $this->patchJson("/api/projects/{$p->id}/time-entries/{$te->id}", ['minutes' => 45])->assertOk();
    expect($te->fresh()->minutes)->toBe(45);

    $other = Project::factory()->create();
    $this->patchJson("/api/projects/{$other->id}/time-entries/{$te->id}", ['minutes' => 30])->assertNotFound();

    $foreignTask = Task::factory()->create(['project_id' => $other->id]);
    $this->postJson("/api/projects/{$p->id}/time-entries", [
        'worked_on' => now()->toDateString(), 'minutes' => 15, 'description' => 'x', 'task_id' => $foreignTask->id,
    ])->assertStatus(422)->assertJsonValidationErrors(['task_id']);

    $this->deleteJson("/api/projects/{$p->id}/time-entries/{$te->id}")->assertNoContent();
    expect(TimeEntry::find($te->id))->toBeNull()
        ->and(TimeEntry::withTrashed()->find($te->id))->not->toBeNull();
});
