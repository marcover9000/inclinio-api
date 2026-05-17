<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Models\Task;

class UpdateTask
{
    public function __invoke(Task $task, array $data): Task
    {
        $task->update([
            'title' => $data['title'] ?? $task->title,
            'status' => $data['status'] ?? $task->status,
            'estimate_hours' => array_key_exists('estimate_hours', $data) ? $data['estimate_hours'] : $task->estimate_hours,
        ]);

        return $task->fresh();
    }
}
