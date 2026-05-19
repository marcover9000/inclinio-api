<?php

namespace App\Modules\Projects\Domain\Dashboard;

use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Agregador del dashboard de cartera (global, no user-scoped). Fase 3d.
 * El rang (?from,?to) afecta NOMÉS hours_by_project i billable_split;
 * els KPIs són una instantània de cartera independent del filtre.
 * Supòsit build-for-now: cartera mono-divisa (EUR).
 */
class TimeDashboard
{
    private const TOP_PROJECTS = 8;

    public function __construct(
        private readonly ?string $from = null,
        private readonly ?string $to = null,
    ) {
    }

    /**
     * @return array{
     *   kpis: array{hours_today: float, hours_week: float, hours_month: float, theoretical_cost: Money, real_margin: Money},
     *   hours_by_project: list<array{project_id:int, project_name:string, hours:float}>,
     *   billable_split: array{billable_hours: float, internal_hours: float},
     *   range: array{from: string|null, to: string|null}
     * }
     */
    public function result(): array
    {
        return [
            'kpis' => $this->kpis(),
            'hours_by_project' => $this->hoursByProject(),
            'billable_split' => $this->billableSplit(),
            'range' => ['from' => $this->from, 'to' => $this->to],
        ];
    }

    /** @return array{hours_today: float, hours_week: float, hours_month: float, theoretical_cost: Money, real_margin: Money} */
    private function kpis(): array
    {
        $now = Carbon::now();

        $cost = Money::zero();
        $margin = Money::zero();
        foreach (Project::query()->with(['timeEntries', 'hoursPacks'])->get() as $project) {
            $cost = $cost->add($project->theoreticalCost());
            $m = $project->realMargin();
            if ($m !== null) {
                $margin = $margin->add($m);
            }
        }

        return [
            'hours_today' => $this->minutesToHours(
                (int) TimeEntry::query()->whereDate('worked_on', $now->toDateString())->sum('minutes'),
            ),
            'hours_week' => $this->minutesToHours(
                (int) TimeEntry::query()->whereBetween('worked_on', [
                    $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                    $now->copy()->endOfWeek(Carbon::SUNDAY)->toDateString(),
                ])->sum('minutes'),
            ),
            'hours_month' => $this->minutesToHours(
                (int) TimeEntry::query()->whereBetween('worked_on', [
                    $now->copy()->startOfMonth()->toDateString(),
                    $now->copy()->endOfMonth()->toDateString(),
                ])->sum('minutes'),
            ),
            'theoretical_cost' => $cost,
            'real_margin' => $margin,
        ];
    }

    /** @return list<array{project_id:int, project_name:string, hours:float}> */
    private function hoursByProject(): array
    {
        $rows = $this->rangedEntries()
            ->selectRaw('project_id, SUM(minutes) as mins')
            ->groupBy('project_id')
            ->pluck('mins', 'project_id');

        if ($rows->isEmpty()) {
            return [];
        }

        $names = Project::withTrashed()->whereIn('id', $rows->keys())->pluck('name', 'id');

        $all = $rows
            ->map(fn ($mins, $id) => [
                'project_id' => (int) $id,
                'project_name' => $names[$id] ?? "Projecte #{$id}",
                'hours' => $this->minutesToHours((int) $mins),
            ])
            ->sortByDesc('hours')
            ->values();

        if ($all->count() <= self::TOP_PROJECTS) {
            return $all->all();
        }

        $top = $all->take(self::TOP_PROJECTS);
        $rest = $all->slice(self::TOP_PROJECTS);

        return $top->push([
            'project_id' => 0,
            'project_name' => 'Altres',
            'hours' => round((float) $rest->sum('hours'), 2),
        ])->all();
    }

    /** @return array{billable_hours: float, internal_hours: float} */
    private function billableSplit(): array
    {
        $rows = $this->rangedEntries()
            ->join('projects', 'projects.id', '=', 'time_entries.project_id')
            ->selectRaw('projects.is_internal as is_internal, SUM(time_entries.minutes) as mins')
            ->groupBy('projects.is_internal')
            ->pluck('mins', 'is_internal');

        $billable = 0;
        $internal = 0;
        foreach ($rows as $isInternal => $mins) {
            if ((int) $isInternal === 1) {
                $internal += (int) $mins;
            } else {
                $billable += (int) $mins;
            }
        }

        return [
            'billable_hours' => $this->minutesToHours($billable),
            'internal_hours' => $this->minutesToHours($internal),
        ];
    }

    private function rangedEntries(): Builder
    {
        return TimeEntry::query()
            ->when($this->from, fn (Builder $q, string $f) => $q->whereDate('worked_on', '>=', $f))
            ->when($this->to, fn (Builder $q, string $t) => $q->whereDate('worked_on', '<=', $t));
    }

    private function minutesToHours(int $minutes): float
    {
        return round($minutes / 60, 2);
    }
}
