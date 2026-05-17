<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Application\Actions\ConvertLeadToProject;
use App\Modules\Projects\Domain\Exceptions\LeadNotConvertible;
use App\Modules\Projects\Http\Controllers\Concerns\BuildsPackPayload;
use App\Modules\Projects\Http\Requests\ConvertLeadToProjectRequest;
use App\Modules\Projects\Http\Resources\ProjectResource;
use Illuminate\Http\JsonResponse;

class LeadProjectController extends Controller
{
    use BuildsPackPayload;

    public function store(
        ConvertLeadToProjectRequest $request,
        Lead $lead,
        ConvertLeadToProject $action,
    ): JsonResponse {
        $data = $request->validated();

        $payload = [
            'mode' => $data['mode'],
            'name' => $data['name'] ?? null,
            'project_id' => isset($data['project_id']) ? (int) $data['project_id'] : null,
            'pack' => $this->packFromInput($data['pack']),
        ];

        try {
            $project = $action($lead, $payload);
        } catch (LeadNotConvertible $e) {
            abort(422, $e->getMessage());
        }

        return ProjectResource::make(
            $project->load(['clientCompany', 'clientPerson', 'hoursPacks.sourceLead'])
        )->response()->setStatusCode(201);
    }
}
