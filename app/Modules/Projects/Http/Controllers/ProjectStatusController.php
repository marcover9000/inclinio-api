<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\ChangeProjectStatus;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Exceptions\InvalidProjectTransition;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Http\Requests\ChangeProjectStatusRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;

class ProjectStatusController extends Controller
{
    public function __invoke(
        ChangeProjectStatusRequest $request,
        Project $project,
        ChangeProjectStatus $action,
    ): ProjectResource {
        try {
            $updated = $action($project, ProjectStatus::from($request->validated('status')));
        } catch (InvalidProjectTransition $e) {
            abort(422, $e->getMessage());
        }

        return ProjectResource::make($updated->load(['clientCompany', 'clientPerson', 'hoursPacks']));
    }
}
