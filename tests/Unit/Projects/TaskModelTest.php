<?php

use App\Modules\Projects\Domain\Enums\TaskStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;
use App\Modules\Projects\Domain\Models\TimeEntry;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('belongs to a project and casts status + estimate', function () {
    $p = Project::factory()->create();
    $t = Task::factory()->create(['project_id' => $p->id, 'status' => TaskStatus::Doing, 'estimate_hours' => 8.5]);

    expect($t->project->id)->toBe($p->id)
        ->and($t->status)->toBe(TaskStatus::Doing)
        ->and((float) $t->estimate_hours)->toBe(8.5);
});

it('has many time entries and soft-deletes', function () {
    $t = Task::factory()->create();
    TimeEntry::factory()->create(['project_id' => $t->project_id, 'task_id' => $t->id]);

    expect($t->timeEntries)->toHaveCount(1);

    $t->delete();
    expect(Task::find($t->id))->toBeNull()
        ->and(Task::withTrashed()->find($t->id))->not->toBeNull();
});
