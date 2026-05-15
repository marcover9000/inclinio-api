<?php

namespace App\Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contacts\Application\Actions\CreatePerson;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Contacts\Http\Requests\CreatePersonRequest;
use App\Modules\Contacts\Http\Requests\UpdatePersonRequest;
use App\Modules\Contacts\Http\Resources\PersonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Person::query()->with('company');
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->query('company_id')) {
            $query->where('company_id', $request->query('company_id'));
        }
        $query->filterIsClient($request->has('is_client') ? $request->boolean('is_client') : null);
        return PersonResource::collection($query->paginate(min((int) $request->query('per_page', 20), 100)));
    }

    public function store(CreatePersonRequest $request, CreatePerson $createPerson): JsonResponse
    {
        $person = $createPerson($request->validated());
        return PersonResource::make($person->load('company'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Person $person): PersonResource
    {
        return PersonResource::make($person->load([
            'company',
            'leads' => fn ($q) => $q->orderByDesc('created_at'),
        ]));
    }

    public function update(UpdatePersonRequest $request, Person $person): PersonResource
    {
        DB::transaction(function () use ($request, $person) {
            $person->update($request->validated());
        });
        return PersonResource::make($person->fresh()->load('company'));
    }

    public function destroy(Person $person): Response|JsonResponse
    {
        if ($person->leads()->active()->exists()) {
            return response()->json([
                'message' => "Aquesta persona té leads actius. Tanca'ls com a Guanyat o Perdut abans d'eliminar-la.",
            ], 422);
        }
        $person->delete();
        return response()->noContent();
    }
}
