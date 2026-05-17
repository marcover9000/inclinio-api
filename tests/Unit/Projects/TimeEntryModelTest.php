<?php

use App\Modules\Projects\Domain\Models\TimeEntry;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('casts worked_on and minutes, relates project/task/user, soft-deletes', function () {
    $te = TimeEntry::factory()->create(['minutes' => 90]);

    expect($te->minutes)->toBe(90)
        ->and($te->worked_on->toDateString())->toBe(now()->toDateString())
        ->and($te->project)->not->toBeNull()
        ->and($te->user)->not->toBeNull();

    $te->delete();
    expect(TimeEntry::find($te->id))->toBeNull()
        ->and(TimeEntry::withTrashed()->find($te->id))->not->toBeNull();
});
