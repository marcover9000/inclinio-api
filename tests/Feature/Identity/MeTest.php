<?php

use App\Modules\Identity\Domain\Models\User;

/*
 * Tests del endpoint GET /api/me.
 * Retorna l'usuari actual autenticat amb la seva role.
 */

it('retorna 401 si no hi ha usuari autenticat', function () {
    $this->getJson('/api/me')->assertUnauthorized();
});

it('retorna les dades de l\'usuari autenticat', function () {
    $user = User::factory()->admin()->create([
        'name' => 'Marc',
        'email' => 'marc@inclinio.test',
    ]);

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'name' => 'Marc',
                'email' => 'marc@inclinio.test',
                'role' => 'admin',
            ],
        ]);
});

it('inclou el rol staff correctament', function () {
    $user = User::factory()->staff()->create();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('data.role', 'staff');
});
