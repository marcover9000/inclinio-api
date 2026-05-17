<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Exceptions\LeadNotConvertible;
use App\Modules\Projects\Domain\Models\Project;
use Illuminate\Support\Facades\DB;

/**
 * Converteix un lead 'won' en projecte (spec §5):
 *  - mode 'new'    → CreateProject + HoursPack #1 (source_lead_id = lead).
 *  - mode 'extend' → AddHoursPack a un projecte existent (reobre si cal).
 * El "mateix client" és un avís NO bloquejant: es resol al frontend
 * (recomanació), no aquí. El backend mai 422 per discrepància de client.
 */
class ConvertLeadToProject
{
    public function __construct(
        private readonly CreateProject $createProject,
        private readonly AddHoursPack $addHoursPack,
    ) {
    }

    /**
     * @param  array{
     *   mode:'new'|'extend',
     *   name?:string,
     *   project_id?:int,
     *   pack:array{billing_mode?:\App\Modules\Projects\Domain\Enums\BillingMode|string,hours?:?int,price?:\App\Modules\Shared\Domain\ValueObjects\Money,hourly_rate?:\App\Modules\Shared\Domain\ValueObjects\Money,reason:string,dated_on?:?string}
     * } $data
     */
    public function __invoke(Lead $lead, array $data): Project
    {
        if ($lead->status !== LeadStatus::Won) {
            throw new LeadNotConvertible($lead->status);
        }

        return DB::transaction(function () use ($lead, $data) {
            $pack = [
                ...$data['pack'],
                'source_lead_id' => $lead->id,
            ];

            if ($data['mode'] === 'extend') {
                $project = Project::findOrFail($data['project_id']);
                ($this->addHoursPack)($project, $pack);

                return $project->fresh();
            }

            return ($this->createProject)([
                'name' => $data['name'],
                'is_internal' => false,
                'client_company_id' => $lead->company_id,
                'client_person_id' => $lead->company_id ? null : $lead->person_id,
                'pack' => $pack,
            ]);
        });
    }
}
