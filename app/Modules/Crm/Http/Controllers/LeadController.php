<?php

namespace App\Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Application\Actions\CreateLead;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Http\Requests\CreateLeadRequest;
use App\Modules\Crm\Http\Resources\LeadResource;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    public function store(CreateLeadRequest $request, CreateLead $createLead): JsonResponse
    {
        $data = $request->validated();
        $lead = $createLead([
            'person' => $data['person'],
            'company' => $data['company'] ?? null,
            'lead' => $data['lead'],
            'source' => LeadSource::Manual,
        ]);

        return LeadResource::make($lead->load(['person', 'company']))
            ->response()
            ->setStatusCode(201);
    }
}
