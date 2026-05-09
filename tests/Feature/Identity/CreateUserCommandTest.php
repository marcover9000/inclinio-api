<?php

use App\Modules\Identity\Domain\Models\User;

/*
 * Tests de la comanda Artisan `user:create`.
 * Verifica creació via CLI, validació de duplicats i mismatch de password.
 */

it('crea un usuari amb la comanda artisan', function () {
    $this->artisan('user:create', ['email' => 'cli@inclinio.test', '--role' => 'admin'])
        ->expectsQuestion('Nom complet', 'Marc CLI')
        ->expectsQuestion('Contrasenya', 'secret123')
        ->expectsQuestion('Confirmar contrasenya', 'secret123')
        ->expectsOutput("Usuari creat: cli@inclinio.test (rol: admin)")
        ->assertSuccessful();

    $user = User::where('email', 'cli@inclinio.test')->first();
    expect($user)->not->toBeNull();
    expect($user->isAdmin())->toBeTrue();
});

it('falla si l\'email ja existeix', function () {
    User::factory()->create(['email' => 'exists@inclinio.test']);

    $this->artisan('user:create', ['email' => 'exists@inclinio.test'])
        ->expectsQuestion('Nom complet', 'Test')
        ->expectsQuestion('Contrasenya', 'secret123')
        ->expectsQuestion('Confirmar contrasenya', 'secret123')
        ->expectsOutputToContain('email')
        ->assertFailed();
});

it('rebutja si les dues contrasenyes no coincideixen', function () {
    $this->artisan('user:create', ['email' => 'mismatch@inclinio.test'])
        ->expectsQuestion('Nom complet', 'Test')
        ->expectsQuestion('Contrasenya', 'secret123')
        ->expectsQuestion('Confirmar contrasenya', 'different456')
        ->expectsOutputToContain('no coincideixen')
        ->assertFailed();
});
