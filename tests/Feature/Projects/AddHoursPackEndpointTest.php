<?php

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Identity\Domain\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('requires authentication', function () {
    $p = Project::factory()->create();
    $this->postJson("/api/projects/{$p->id}/packs", [])->assertUnauthorized();
});

it('adds an ampliació pack and returns the project', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Active)->create();
    $r = $this->actingAs($this->admin)->postJson("/api/projects/{$p->id}/packs", [
        'hours' => 10, 'price_cents' => 120000, 'currency' => 'EUR', 'reason' => 'Ampliació SEO',
    ]);
    $r->assertCreated();
    expect($r->json('data.budgeted_hours'))->toBe(10)
        ->and($r->json('data.total_price.cents'))->toBe(120000);
});

it('reopens a done project when an ampliació is added', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Done)->create();
    $this->actingAs($this->admin)->postJson("/api/projects/{$p->id}/packs", [
        'hours' => 5, 'price_cents' => 60000, 'currency' => 'EUR', 'reason' => 'Ampliació',
    ])->assertCreated()->assertJsonPath('data.status', 'active');
});

it('validates the pack payload', function () {
    $p = Project::factory()->create();
    $this->actingAs($this->admin)->postJson("/api/projects/{$p->id}/packs", [
        'hours' => 0, 'price_cents' => -1, 'currency' => 'EURO', 'reason' => '',
    ])->assertJsonValidationErrors(['hours', 'price_cents', 'currency', 'reason']);
});
