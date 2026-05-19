<?php

use App\Modules\Projects\Domain\Dashboard\TimeDashboard;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

afterEach(fn () => Carbon::setTestNow());

it('returns zeroed structure with no data', function () {
    Carbon::setTestNow('2026-05-19'); // dimarts
    $r = (new TimeDashboard())->result();

    expect($r['kpis']['hours_today'])->toBe(0.0)
        ->and($r['kpis']['hours_week'])->toBe(0.0)
        ->and($r['kpis']['hours_month'])->toBe(0.0)
        ->and($r['kpis']['theoretical_cost'])->toBeInstanceOf(Money::class)
        ->and($r['kpis']['theoretical_cost']->amountCents)->toBe(0)
        ->and($r['kpis']['real_margin']->amountCents)->toBe(0)
        ->and($r['hours_by_project'])->toBe([])
        ->and($r['billable_split'])->toBe(['billable_hours' => 0.0, 'internal_hours' => 0.0])
        ->and($r['range'])->toBe(['from' => null, 'to' => null]);
});

it('aggregates hours_by_project within range, sorted desc, with Altres bucket beyond top 8', function () {
    Carbon::setTestNow('2026-05-19');
    // 9 projectes amb hores decreixents; el 9è ha d'anar a "Altres"
    foreach (range(1, 9) as $i) {
        $p = Project::factory()->create(['name' => "P{$i}"]);
        TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => (10 - $i) * 60, 'worked_on' => '2026-05-10']);
    }
    // entrada FORA del rang: no s'ha de comptar
    $px = Project::factory()->create(['name' => 'Fora']);
    TimeEntry::factory()->create(['project_id' => $px->id, 'minutes' => 99 * 60, 'worked_on' => '2026-01-01']);

    $rows = (new TimeDashboard('2026-05-01', '2026-05-31'))->result()['hours_by_project'];

    expect($rows)->toHaveCount(9)                       // 8 top + Altres
        ->and($rows[0]['project_name'])->toBe('P1')     // més hores → primer (desc)
        ->and($rows[0]['hours'])->toBe(9.0)             // (10-1)*60 = 540min = 9h
        ->and($rows[8]['project_name'])->toBe('Altres')
        ->and($rows[8]['project_id'])->toBe(0)
        ->and($rows[8]['hours'])->toBe(1.0)             // només P9: (10-9)*60=60min=1h
        ->and(collect($rows)->pluck('project_name'))->not->toContain('Fora');
});

it('splits billable vs internal hours within range', function () {
    Carbon::setTestNow('2026-05-19');
    $client = Project::factory()->create();             // is_internal=false
    $internal = Project::factory()->internal()->create();
    TimeEntry::factory()->create(['project_id' => $client->id, 'minutes' => 120, 'worked_on' => '2026-05-10']);
    TimeEntry::factory()->create(['project_id' => $internal->id, 'minutes' => 60, 'worked_on' => '2026-05-10']);
    TimeEntry::factory()->create(['project_id' => $client->id, 'minutes' => 600, 'worked_on' => '2026-01-01']); // fora rang

    $split = (new TimeDashboard('2026-05-01', '2026-05-31'))->result()['billable_split'];

    expect($split)->toBe(['billable_hours' => 2.0, 'internal_hours' => 1.0]);
});

it('kpis hours_today/week/month use fixed windows independent of the range filter', function () {
    Carbon::setTestNow('2026-05-19'); // dimarts; setmana 2026-05-18..05-24; mes 2026-05-01..05-31
    $p = Project::factory()->create();
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 60, 'worked_on' => '2026-05-19']); // avui
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 120, 'worked_on' => '2026-05-18']); // setmana, no avui
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 180, 'worked_on' => '2026-05-02']); // mes, no setmana

    // rang restrictiu que NO ha d'afectar els KPIs
    $k = (new TimeDashboard('2026-05-19', '2026-05-19'))->result()['kpis'];

    expect($k['hours_today'])->toBe(1.0)
        ->and($k['hours_week'])->toBe(3.0)   // 60+120 min
        ->and($k['hours_month'])->toBe(6.0); // 60+120+180 min
});

it('theoretical_cost sums all projects; real_margin sums only non-internal', function () {
    Carbon::setTestNow('2026-05-19');
    $client = Project::factory()->create(['shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    HoursPack::factory()->create(['project_id' => $client->id, 'price' => Money::fromCents(100000, 'EUR'), 'hours' => 10]);
    TimeEntry::factory()->create(['project_id' => $client->id, 'minutes' => 600]); // 10h -> cost 30000, marge 70000

    $internal = Project::factory()->internal()->create(['shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    TimeEntry::factory()->create(['project_id' => $internal->id, 'minutes' => 60]); // 1h -> cost 3000, marge null

    $k = (new TimeDashboard())->result()['kpis'];

    expect($k['theoretical_cost']->amountCents)->toBe(33000) // 30000 + 3000
        ->and($k['real_margin']->amountCents)->toBe(70000);  // només el client
});
