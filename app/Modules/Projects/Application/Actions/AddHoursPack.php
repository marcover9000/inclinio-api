<?php

namespace App\Modules\Projects\Application\Actions;

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

class AddHoursPack
{
    /**
     * @param  array{hours:int,price:Money,reason:string,dated_on?:?string,source_lead_id?:?int} $pack
     */
    public function __invoke(Project $project, array $pack): HoursPack
    {
        return DB::transaction(function () use ($project, $pack) {
            // Una ampliació sobre un projecte tancat el reobre (spec §5b/§6).
            if (in_array($project->status, [ProjectStatus::Done, ProjectStatus::Archived], true)) {
                $project->update(['status' => ProjectStatus::Active]);
            }

            return $project->hoursPacks()->create([
                'hours' => $pack['hours'],
                'price' => $pack['price'],
                'reason' => $pack['reason'],
                'dated_on' => $pack['dated_on'] ?? now()->toDateString(),
                'source_lead_id' => $pack['source_lead_id'] ?? null,
            ]);
        });
    }
}
