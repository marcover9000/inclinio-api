<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\AddHoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Http\Controllers\Concerns\BuildsPackPayload;
use App\Modules\Projects\Http\Requests\AddHoursPackRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;

class ProjectHoursPackController extends Controller
{
    use BuildsPackPayload;

    public function store(AddHoursPackRequest $request, Project $project, AddHoursPack $action): JsonResponse
    {
        $data = $request->validated();

        $action($project, $this->packFromInput($data));

        return ProjectResource::make(
            $project->fresh()->load(['clientCompany', 'clientPerson', 'hoursPacks'])
        )->response()->setStatusCode(201);
    }
}
