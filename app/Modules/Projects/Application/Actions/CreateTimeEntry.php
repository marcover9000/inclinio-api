<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;

class CreateTimeEntry
{
    public function __invoke(Project $project, int $userId, array $data): TimeEntry
    {
        return $project->timeEntries()->create([
            'task_id' => $data['task_id'] ?? null,
            'user_id' => $userId,
            'worked_on' => $data['worked_on'],
            'minutes' => (int) $data['minutes'],
            'description' => $data['description'] ?? '',
        ]);
    }
}
