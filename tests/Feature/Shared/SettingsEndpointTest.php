<?php

use App\Modules\Identity\Domain\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('staff cannot read or write settings (403)', function () {
    $this->actingAs(User::factory()->staff()->create());
    $this->getJson('/api/settings')->assertForbidden();
    $this->patchJson('/api/settings', ['shadow_rate_cents' => 4000, 'currency' => 'EUR'])->assertForbidden();
});

it('admin reads and updates the global shadow rate', function () {
    actingAsAdmin();

    $this->getJson('/api/settings')
        ->assertOk()->assertJsonPath('data.shadow_rate.cents', 3000);

    $this->patchJson('/api/settings', ['shadow_rate_cents' => 4200, 'currency' => 'EUR'])
        ->assertOk()->assertJsonPath('data.shadow_rate.cents', 4200);

    $this->getJson('/api/settings')->assertJsonPath('data.shadow_rate.cents', 4200);
});
