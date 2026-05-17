<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Models\TimeEntry;

class DeleteTimeEntry
{
    public function __invoke(TimeEntry $entry): void
    {
        $entry->delete();
    }
}
