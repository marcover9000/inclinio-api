<?php

namespace App\Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Application\Actions\CreateLead;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Http\Requests\CreatePublicLeadRequest;
use Illuminate\Http\JsonResponse;

class PublicLeadController extends Controller
{
    public function __invoke(CreatePublicLeadRequest $request, CreateLead $createLead): JsonResponse
    {
        if (!empty($request->input('_hp'))) {
            return response()->json(['message' => 'received'], 201);
        }

        $data = $request->validated();
        $createLead([
            'person' => [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'position' => $data['position'] ?? null,
            ],
            'company' => !empty($data['company_name']) ? ['name' => $data['company_name']] : null,
            'lead' => [
                'message' => $data['message'],
                'tags' => $data['tags'] ?? [],
            ],
            'source' => LeadSource::WebForm,
        ]);

        return response()->json(['message' => 'received'], 201);
    }
}
