<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Models\TimeEntry;

class UpdateTimeEntry
{
    public function __invoke(TimeEntry $entry, array $data): TimeEntry
    {
        $entry->update([
            'task_id' => array_key_exists('task_id', $data) ? $data['task_id'] : $entry->task_id,
            'worked_on' => $data['worked_on'] ?? $entry->worked_on,
            'minutes' => isset($data['minutes']) ? (int) $data['minutes'] : $entry->minutes,
            'description' => $data['description'] ?? $entry->description,
        ]);

        return $entry->fresh();
    }
}
