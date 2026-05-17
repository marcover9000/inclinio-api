<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\CreateTask;
use App\Modules\Projects\Application\Actions\DeleteTask;
use App\Modules\Projects\Application\Actions\UpdateTask;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\Task;
use App\Modules\Projects\Http\Requests\StoreTaskRequest;
use App\Modules\Projects\Http\Requests\UpdateTaskRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectTaskController extends Controller
{
    private const RELATIONS = ['clientCompany', 'clientPerson', 'hoursPacks.sourceLead', 'tasks.timeEntries', 'timeEntries'];

    public function store(StoreTaskRequest $request, Project $project, CreateTask $action): JsonResponse
    {
        $action($project, $request->validated());

        return ProjectResource::make($project->fresh()->load(self::RELATIONS))
            ->response()->setStatusCode(201);
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task, UpdateTask $action): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $action($task, $request->validated());

        return ProjectResource::make($project->fresh()->load(self::RELATIONS))
            ->response()->setStatusCode(200);
    }

    public function destroy(Project $project, Task $task, DeleteTask $action): Response
    {
        abort_unless($task->project_id === $project->id, 404);

        $action($task);

        return response()->noContent();
    }
}
