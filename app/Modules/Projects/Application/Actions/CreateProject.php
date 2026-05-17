<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

class CreateProject
{
    public function __construct(private readonly AddHoursPack $addHoursPack)
    {
    }

    /**
     * @param array{
     *   name:string, is_internal?:bool,
     *   client_company_id?:?int, client_person_id?:?int,
     *   shadow_rate_override?:?Money, started_at?:?string, due_at?:?string,
     *   pack?:array<string,mixed>
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
                ($this->addHoursPack)($project, $data['pack']);
            }

            return $project->fresh();
        });
    }
}
