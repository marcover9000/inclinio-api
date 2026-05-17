<?php

use App\Modules\Projects\Domain\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('adds billing_mode + hourly_rate columns to hours_packs', function () {
    expect(Schema::hasColumns('hours_packs', [
        'billing_mode', 'hourly_rate_cents', 'hourly_rate_currency',
    ]))->toBeTrue();
});

it('makes hours nullable (fixed-price packs may have no hours)', function () {
    $project = Project::factory()->create();
    $id = DB::table('hours_packs')->insertGetId([
        'project_id' => $project->id,
        'hours' => null,
        'price_cents' => 400000,
        'price_currency' => 'EUR',
        'dated_on' => now()->toDateString(),
        'reason' => 'Preu tancat sense hores',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    expect(DB::table('hours_packs')->where('id', $id)->value('hours'))->toBeNull()
        ->and(DB::table('hours_packs')->where('id', $id)->value('billing_mode'))->toBe('fixed');
});
