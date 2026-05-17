<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Exceptions\InvalidProjectTransition;
use App\Modules\Projects\Domain\Models\Project;
use Illuminate\Support\Facades\DB;

class ChangeProjectStatus
{
    public function __invoke(Project $project, ProjectStatus $target): Project
    {
        if (! $project->status->canTransitionTo($target)) {
            throw new InvalidProjectTransition($project->status, $target);
        }

        return DB::transaction(function () use ($project, $target) {
            $project->update(['status' => $target]);

            return $project->fresh();
        });
    }
}
