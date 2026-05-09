<?php

use App\Modules\Identity\Domain\Models\User;

/*
 * Tests del endpoint POST /logout.
 */

it('tanca la sessió de l\'usuari autenticat', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/logout')
        ->assertNoContent();

    $this->assertGuest('web');
});

it('retorna 401 si no hi ha usuari autenticat', function () {
    $this->postJson('/logout')->assertUnauthorized();
});
