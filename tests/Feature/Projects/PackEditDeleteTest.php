<?php

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('requires authentication', function () {
    $p = Project::factory()->create();
    $pack = HoursPack::factory()->create(['project_id' => $p->id]);

    $this->patchJson("/api/projects/{$p->id}/packs/{$pack->id}", [])->assertUnauthorized();
    $this->deleteJson("/api/projects/{$p->id}/packs/{$pack->id}")->assertUnauthorized();
});

it('edits a fixed pack price and reason', function () {
    $p = Project::factory()->create();
    $pack = HoursPack::factory()->create(['project_id' => $p->id]);

    $r = $this->actingAs($this->admin)->patchJson("/api/projects/{$p->id}/packs/{$pack->id}", [
        'billing_mode' => 'fixed',
        'price_cents' => 750000,
        'currency' => 'EUR',
        'reason' => 'Correcció',
    ]);

    $r->assertOk();
    expect($pack->fresh()->price->amountCents)->toBe(750000)
        ->and($pack->fresh()->reason)->toBe('Correcció');
    $r->assertJsonPath('data.total_price.cents', 750000);
});

it('switches a pack to hourly via PATCH and recomputes price', function () {
    $p = Project::factory()->create();
    $pack = HoursPack::factory()->create(['project_id' => $p->id]);

    $r = $this->actingAs($this->admin)->patchJson("/api/projects/{$p->id}/packs/{$pack->id}", [
        'billing_mode' => 'hourly',
        'hours' => 10,
        'hourly_rate_cents' => 5000,
        'currency' => 'EUR',
        'reason' => 'A hores',
    ]);

    $r->assertOk();
    $fresh = $pack->fresh();
    expect($fresh->billing_mode->value)->toBe('hourly')
        ->and($fresh->hourly_rate->amountCents)->toBe(5000)
        ->and($fresh->price->amountCents)->toBe(50000);
});

it('does not reopen a done project when a pack is edited', function () {
    $p = Project::factory()->withStatus(ProjectStatus::Done)->create();
    $pack = HoursPack::factory()->create(['project_id' => $p->id]);

    $r = $this->actingAs($this->admin)->patchJson("/api/projects/{$p->id}/packs/{$pack->id}", [
        'billing_mode' => 'fixed',
        'price_cents' => 500000,
        'currency' => 'EUR',
        'reason' => 'Correcció',
    ]);

    $r->assertOk();
    $r->assertJsonPath('data.status', 'done');
    expect($p->fresh()->status)->toEqual(ProjectStatus::Done);
});

it('404s when the pack does not belong to the project', function () {
    $projectA = Project::factory()->create();
    $projectB = Project::factory()->create();
    $packB = HoursPack::factory()->create(['project_id' => $projectB->id]);

    $validBody = [
        'billing_mode' => 'fixed',
        'price_cents' => 100000,
        'currency' => 'EUR',
        'reason' => 'Test',
    ];

    $this->actingAs($this->admin)
        ->patchJson("/api/projects/{$projectA->id}/packs/{$packB->id}", $validBody)
        ->assertNotFound();

    $this->actingAs($this->admin)
        ->deleteJson("/api/projects/{$projectA->id}/packs/{$packB->id}")
        ->assertNotFound();
});

it('soft-deletes a pack', function () {
    $p = Project::factory()->create();
    $pack = HoursPack::factory()->create(['project_id' => $p->id]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/projects/{$p->id}/packs/{$pack->id}")
        ->assertNoContent();

    expect(HoursPack::find($pack->id))->toBeNull()
        ->and(HoursPack::withTrashed()->find($pack->id))->not->toBeNull();
});

it('validates the PATCH payload', function () {
    $p = Project::factory()->create();
    $pack = HoursPack::factory()->create(['project_id' => $p->id]);

    $this->actingAs($this->admin)
        ->patchJson("/api/projects/{$p->id}/packs/{$pack->id}", [
            'billing_mode' => 'hourly',
            'currency' => 'EURO',
            'reason' => '',
        ])
        ->assertJsonValidationErrors(['hours', 'hourly_rate_cents', 'currency', 'reason']);
});
