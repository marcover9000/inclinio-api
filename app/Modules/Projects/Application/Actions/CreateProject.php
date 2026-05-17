<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

class CreateProject
{
    /**
     * @param  array{
     *   name:string,
     *   is_internal?:bool,
     *   client_company_id?:?int,
     *   client_person_id?:?int,
     *   shadow_rate_override?:?Money,
     *   started_at?:?string,
     *   due_at?:?string,
     *   pack?:?array{hours:int,price:Money,reason:string,dated_on?:?string,source_lead_id?:?int}
     * } $data
     */
    public function __invoke(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $project = Project::create([
                'name' => $data['name'],
                'status' => ProjectStatus::Active,
                'is_internal' => $data['is_internal'] ?? false,
                'client_company_id' => $data['client_company_id'] ?? null,
                'client_person_id' => $data['client_person_id'] ?? null,
                'shadow_rate_override' => $data['shadow_rate_override'] ?? null,
                'started_at' => $data['started_at'] ?? null,
                'due_at' => $data['due_at'] ?? null,
            ]);

            if (! empty($data['pack'])) {
                $project->hoursPacks()->create([
                    'hours' => $data['pack']['hours'],
                    'price' => $data['pack']['price'],
                    'reason' => $data['pack']['reason'],
                    'dated_on' => $data['pack']['dated_on'] ?? now()->toDateString(),
                    'source_lead_id' => $data['pack']['source_lead_id'] ?? null,
                ]);
            }

            return $project->fresh();
        });
    }
}
