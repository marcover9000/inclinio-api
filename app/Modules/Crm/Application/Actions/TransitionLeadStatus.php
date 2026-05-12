<?php

namespace App\Modules\Crm\Application\Actions;

use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Events\LeadConverted;
use App\Modules\Crm\Domain\Exceptions\InvalidLeadTransition;
use App\Modules\Crm\Domain\Models\Lead;
use Illuminate\Support\Facades\DB;

class TransitionLeadStatus
{
    public function __invoke(Lead $lead, LeadStatus $target): Lead
    {
        if (!$lead->status->canTransitionTo($target)) {
            throw new InvalidLeadTransition($lead->status, $target);
        }

        return DB::transaction(function () use ($lead, $target) {
            $lead->update([
                'status' => $target,
                'status_changed_at' => now(),
            ]);

            if ($target === LeadStatus::Won) {
                LeadConverted::dispatch($lead->fresh());
            }

            return $lead->fresh();
        });
    }
}
