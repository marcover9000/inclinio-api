<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\AddHoursPack;
use App\Modules\Projects\Application\Actions\UpdateHoursPack;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Http\Controllers\Concerns\BuildsPackPayload;
use App\Modules\Projects\Http\Requests\AddHoursPackRequest;
use App\Modules\Projects\Http\Requests\UpdateHoursPackRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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

    public function update(UpdateHoursPackRequest $request, Project $project, HoursPack $pack, UpdateHoursPack $action): JsonResponse
    {
        abort_unless($pack->project_id === $project->id, 404);

        $action($pack, $this->packFromInput($request->validated()));

        return ProjectResource::make(
            $project->fresh()->load(['clientCompany', 'clientPerson', 'hoursPacks'])
        )->response()->setStatusCode(200);
    }

    public function destroy(Project $project, HoursPack $pack): Response
    {
        abort_unless($pack->project_id === $project->id, 404);

        $pack->delete();

        return response()->noContent();
    }
}
