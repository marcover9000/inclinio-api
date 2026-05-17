<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Application\Actions\AddHoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Http\Requests\AddHoursPackRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Http\JsonResponse;

class ProjectHoursPackController extends Controller
{
    public function store(AddHoursPackRequest $request, Project $project, AddHoursPack $action): JsonResponse
    {
        $data = $request->validated();

        $action($project, [
            'hours' => (int) $data['hours'],
            'price' => Money::fromCents((int) $data['price_cents'], $data['currency']),
            'reason' => $data['reason'],
            'dated_on' => $data['dated_on'] ?? null,
            'source_lead_id' => $data['source_lead_id'] ?? null,
        ]);

        return ProjectResource::make(
            $project->fresh()->load(['clientCompany', 'clientPerson', 'hoursPacks'])
        )->response()->setStatusCode(201);
    }
}
