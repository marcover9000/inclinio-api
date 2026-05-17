<?php

namespace Database\Factories;

use App\Modules\Projects\Domain\Enums\TaskStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => $this->faker->sentence(3),
            'status' => TaskStatus::Todo,
            'estimate_hours' => null,
        ];
    }

    public function status(TaskStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
