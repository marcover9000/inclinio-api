<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\CreateTimeEntry;
use App\Modules\Projects\Application\Actions\DeleteTimeEntry;
use App\Modules\Projects\Application\Actions\UpdateTimeEntry;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Projects\Http\Requests\StoreTimeEntryRequest;
use App\Modules\Projects\Http\Requests\UpdateTimeEntryRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectTimeEntryController extends Controller
{
    private const RELATIONS = ['clientCompany', 'clientPerson', 'hoursPacks.sourceLead', 'tasks.timeEntries', 'timeEntries'];

    public function store(StoreTimeEntryRequest $request, Project $project, CreateTimeEntry $action): JsonResponse
    {
        $action($project, $request->user()->id, $request->validated());

        return ProjectResource::make($project->fresh()->load(self::RELATIONS))
            ->response()->setStatusCode(201);
    }

    public function update(UpdateTimeEntryRequest $request, Project $project, TimeEntry $timeEntry, UpdateTimeEntry $action): JsonResponse
    {
        abort_unless($timeEntry->project_id === $project->id, 404);

        $action($timeEntry, $request->validated());

        return ProjectResource::make($project->fresh()->load(self::RELATIONS))
            ->response()->setStatusCode(200);
    }

    public function destroy(Project $project, TimeEntry $timeEntry, DeleteTimeEntry $action): Response
    {
        abort_unless($timeEntry->project_id === $project->id, 404);

        $action($timeEntry);

        return response()->noContent();
    }
}
