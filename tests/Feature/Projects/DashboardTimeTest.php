<?php

use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Projects\Domain\Models\TimeEntry;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Support\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    actingAsAdmin();
    Carbon::setTestNow('2026-05-19');
});

afterEach(fn () => Carbon::setTestNow());

it('requires authentication', function () {
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/dashboard/time')->assertUnauthorized();
});

it('returns the dashboard shape with money serialized as cents/currency/formatted', function () {
    $p = Project::factory()->create(['name' => 'Web', 'shadow_rate_override' => Money::fromCents(3000, 'EUR')]);
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 120, 'worked_on' => '2026-05-19']);

    $this->getJson('/api/dashboard/time')
        ->assertOk()
        ->assertJsonPath('data.kpis.hours_today', 2.0)
        ->assertJsonPath('data.kpis.theoretical_cost.cents', 6000)   // 2h * 3000
        ->assertJsonPath('data.kpis.theoretical_cost.currency', 'EUR')
        ->assertJsonStructure(['data' => [
            'kpis' => ['hours_today', 'hours_week', 'hours_month',
                'theoretical_cost' => ['cents', 'currency', 'formatted'],
                'real_margin' => ['cents', 'currency', 'formatted']],
            'hours_by_project' => [['project_id', 'project_name', 'hours']],
            'billable_split' => ['billable_hours', 'internal_hours'],
            'range' => ['from', 'to'],
        ]])
        ->assertJsonPath('data.hours_by_project.0.project_name', 'Web')
        ->assertJsonPath('data.range.from', null);
});

it('range filter affects charts but not kpis', function () {
    $p = Project::factory()->create(['name' => 'A']);
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 60, 'worked_on' => '2026-05-19']);  // dins mes i rang
    TimeEntry::factory()->create(['project_id' => $p->id, 'minutes' => 120, 'worked_on' => '2026-05-02']); // dins mes, fora rang

    $this->getJson('/api/dashboard/time?from=2026-05-15&to=2026-05-31')
        ->assertOk()
        ->assertJsonPath('data.hours_by_project.0.hours', 1.0) // només l'entrada dins rang
        ->assertJsonPath('data.kpis.hours_month', 3.0)         // mes sencer, ignora el rang
        ->assertJsonPath('data.range.from', '2026-05-15');
});

it('rejects an invalid date param', function () {
    $this->getJson('/api/dashboard/time?from=notadate')->assertStatus(422);
});
