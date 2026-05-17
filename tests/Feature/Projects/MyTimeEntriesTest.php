<?php

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;
use App\Modules\Projects\Domain\Models\TimeEntry;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->me = actingAsAdmin();
});

it('requires authentication', function () {
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/time-entries')->assertUnauthorized();
});

it('returns only the auth user entries with project and task labels, no money', function () {
    $p = Project::factory()->create(['name' => 'Web']);
    $task = Task::factory()->create(['project_id' => $p->id, 'title' => 'Login']);
    TimeEntry::factory()->create(['project_id' => $p->id, 'task_id' => $task->id, 'user_id' => $this->me->id, 'minutes' => 60]);
    $other = User::factory()->admin()->create();
    TimeEntry::factory()->create(['project_id' => $p->id, 'user_id' => $other->id]);

    $this->getJson('/api/time-entries')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.project.name', 'Web')
        ->assertJsonPath('data.0.task.title', 'Login')
        ->assertJsonMissingPath('data.0.price');
});

it('filters by from, to, project_id and task_id', function () {
    $a = Project::factory()->create();
    $b = Project::factory()->create();
    TimeEntry::factory()->create(['project_id' => $a->id, 'user_id' => $this->me->id, 'worked_on' => '2026-05-01']);
    TimeEntry::factory()->create(['project_id' => $b->id, 'user_id' => $this->me->id, 'worked_on' => '2026-05-20']);

    $this->getJson('/api/time-entries?from=2026-05-10')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.project_id', $b->id);

    $this->getJson("/api/time-entries?project_id={$a->id}")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.project_id', $a->id);
});
