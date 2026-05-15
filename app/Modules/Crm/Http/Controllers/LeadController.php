<?php

namespace App\Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Application\Actions\CreateLead;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Http\Requests\CreateLeadRequest;
use App\Modules\Crm\Http\Requests\UpdateLeadRequest;
use App\Modules\Crm\Http\Resources\LeadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LeadController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Lead::query()
            ->with([
                'person' => fn ($q) => $q->withTrashed()->with(['company' => fn ($q2) => $q2->withTrashed()]),
                'company' => fn ($q) => $q->withTrashed(),
            ])
            ->orderByDesc('status_changed_at');

        if ($status = $request->query('status')) {
            $query->whereIn('status', explode(',', $status));
        }

        if ($tags = $request->query('tags')) {
            foreach (explode(',', $tags) as $tag) {
                $query->withTag($tag);
            }
        }

        if ($search = $request->query('search')) {
            $query->whereHas('person', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('company', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        return LeadResource::collection($query->paginateFromRequest());
    }

    public function store(CreateLeadRequest $request, CreateLead $createLead): JsonResponse
    {
        $data = $request->validated();
        $lead = $createLead([
            'person_id' => $data['person_id'] ?? null,
            'person' => $data['person'] ?? null,
            'company' => $data['company'] ?? null,
            'lead' => $data['lead'],
            'source' => LeadSource::Manual,
        ]);

        return LeadResource::make($lead->load(['person', 'company']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Lead $lead): LeadResource
    {
        return LeadResource::make($lead->load([
            'person' => fn ($q) => $q->withTrashed()->with(['company' => fn ($q2) => $q2->withTrashed()]),
            'company' => fn ($q) => $q->withTrashed(),
            'notes.author',
        ]));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): LeadResource
    {
        $lead->update($request->validated());
        return LeadResource::make($lead->fresh()->load(['person.company', 'company']));
    }

    public function destroy(Lead $lead): Response
    {
        $lead->delete();
        return response()->noContent();
    }
}
