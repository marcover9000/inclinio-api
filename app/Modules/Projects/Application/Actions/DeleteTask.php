<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Models\Task;

class DeleteTask
{
    public function __invoke(Task $task): void
    {
        $task->delete();
    }
}
