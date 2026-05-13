<?php

namespace App\Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Http\Requests\CreateCompanyRequest;
use App\Modules\Contacts\Http\Requests\UpdateCompanyRequest;
use App\Modules\Contacts\Http\Resources\CompanyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Company::query();
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($request->has('is_client')) {
            $query->where('is_client', $request->boolean('is_client'));
        }
        return CompanyResource::collection($query->orderBy('name')->paginate(min((int) $request->query('per_page', 20), 100)));
    }

    public function store(CreateCompanyRequest $request): JsonResponse
    {
        $company = Company::create($request->validated());
        return CompanyResource::make($company)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Company $company): CompanyResource
    {
        return CompanyResource::make($company->load([
            'people' => fn ($q) => $q->orderBy('first_name'),
            'leads' => fn ($q) => $q->orderByDesc('created_at'),
        ]));
    }

    public function update(UpdateCompanyRequest $request, Company $company): CompanyResource
    {
        $company->update($request->validated());
        return CompanyResource::make($company->fresh());
    }

    public function destroy(Company $company): Response
    {
        $company->delete();
        return response()->noContent();
    }
}
