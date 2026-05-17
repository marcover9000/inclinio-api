<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;

class CreateTask
{
    public function __invoke(Project $project, array $data): Task
    {
        return $project->tasks()->create([
            'title' => $data['title'],
            'status' => $data['status'] ?? 'todo',
            'estimate_hours' => $data['estimate_hours'] ?? null,
        ]);
    }
}
