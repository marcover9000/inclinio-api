<?php

use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Shared\Domain\ValueObjects\Money;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(fn () => actingAsAdmin());

it('exposes aggregates, tasks and time_entries on show', function () {
    $p = Project::factory()->create(['shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    HoursPack::factory()->create(['project_id' => $p->id, 'price' => Money::fromCents(100000, 'EUR'), 'hours' => 10]);
    $task = Task::factory()->create(['project_id' => $p->id, 'title' => 'A']);
    TimeEntry::factory()->create(['project_id' => $p->id, 'task_id' => $task->id, 'minutes' => 600]);

    $this->getJson("/api/projects/{$p->id}")
        ->assertOk()
        ->assertJsonPath('data.aggregates.consumed_hours', 10)
        ->assertJsonPath('data.aggregates.theoretical_cost.cents', 30000)
        ->assertJsonPath('data.aggregates.real_margin.cents', 70000)
        ->assertJsonPath('data.aggregates.shadow_rate.cents', 3000)
        ->assertJsonPath('data.tasks.0.title', 'A')
        ->assertJsonPath('data.tasks.0.consumed_hours', 10)
        ->assertJsonPath('data.time_entries.0.minutes', 600)
        ->assertJsonMissingPath('data.time_entries.0.price');
});

it('real_margin is null for internal projects', function () {
    $p = Project::factory()->internal()->create();
    $this->getJson("/api/projects/{$p->id}")
        ->assertOk()->assertJsonPath('data.aggregates.real_margin', null);
});
