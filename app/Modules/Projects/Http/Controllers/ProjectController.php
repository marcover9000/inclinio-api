<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\CreateProject;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Http\Controllers\Concerns\BuildsPackPayload;
use App\Modules\Projects\Http\Requests\StoreProjectRequest;
use App\Modules\Projects\Http\Requests\UpdateProjectRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    use BuildsPackPayload;

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Project::query()
            ->with(['clientCompany', 'clientPerson', 'hoursPacks'])
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->ofStatus(explode(',', $status));
        }
        if ($request->filled('client_company_id')) {
            $query->forClientCompany((int) $request->query('client_company_id'));
        }
        if ($request->filled('client_person_id')) {
            $query->forClientPerson((int) $request->query('client_person_id'));
        }
        if ($request->filled('is_internal')) {
            $query->where('is_internal', $request->boolean('is_internal'));
        }
        if ($search = $request->query('search')) {
            $query->search($search);
        }
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        return ProjectResource::collection($query->paginateFromRequest());
    }

    public function store(StoreProjectRequest $request, CreateProject $createProject): JsonResponse
    {
        $data = $request->validated();

        $attributes = [
            'name' => $data['name'],
            'is_internal' => $data['is_internal'] ?? false,
            'client_company_id' => $data['client_company_id'] ?? null,
            'client_person_id' => $data['client_person_id'] ?? null,
            'started_at' => $data['started_at'] ?? null,
            'due_at' => $data['due_at'] ?? null,
        ];

        if (isset($data['shadow_rate_override_cents']) && $data['shadow_rate_override_cents'] !== null) {
            $attributes['shadow_rate_override'] = Money::fromCents(
                (int) $data['shadow_rate_override_cents'],
                $data['shadow_rate_override_currency'] ?? 'EUR',
            );
        }

        if (! empty($data['pack'])) {
            $attributes['pack'] = $this->packFromInput($data['pack']);
        }

        $project = $createProject($attributes);

        return ProjectResource::make($project->load(['clientCompany', 'clientPerson', 'hoursPacks']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        return ProjectResource::make($project->load([
            'clientCompany', 'clientPerson', 'hoursPacks.sourceLead', 'tasks.timeEntries', 'timeEntries',
        ]));
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $data = $request->validated();

        if (array_key_exists('shadow_rate_override_cents', $data)) {
            $data['shadow_rate_override'] = $data['shadow_rate_override_cents'] === null
                ? null
                : Money::fromCents((int) $data['shadow_rate_override_cents'], $data['shadow_rate_override_currency'] ?? 'EUR');
        }
        unset($data['shadow_rate_override_cents'], $data['shadow_rate_override_currency']);

        $project->update($data);

        return ProjectResource::make($project->fresh()->load(['clientCompany', 'clientPerson', 'hoursPacks']));
    }

    public function destroy(Project $project): Response
    {
        $project->delete();

        return response()->noContent();
    }
}
