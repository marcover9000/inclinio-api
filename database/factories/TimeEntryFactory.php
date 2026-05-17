<?php

namespace Database\Factories;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'task_id' => null,
            'user_id' => User::factory(),
            'worked_on' => now()->toDateString(),
            'minutes' => 60,
            'description' => $this->faker->sentence(4),
        ];
    }
}
