<?php

use App\Modules\Identity\Domain\Models\User;
use Database\Seeders\AdminSeeder;

/*
 * Tests del seeder AdminSeeder. Verifica:
 * - Creació del primer admin amb credencials del .env.
 * - Idempotència: executar dos cops no crea duplicats.
 * - Llança excepció clara si falten les variables d'entorn.
 */

it('crea un usuari admin amb les credencials del .env', function () {
    config(['inclinio_admin.email' => 'seeded@inclinio.test', 'inclinio_admin.password' => 'seedpass']);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'seeded@inclinio.test')->first();
    expect($admin)->not->toBeNull();
    expect($admin->isAdmin())->toBeTrue();
});

it('és idempotent: executar dos cops no crea duplicats', function () {
    config(['inclinio_admin.email' => 'idem@inclinio.test', 'inclinio_admin.password' => 'pass1234']);

    $this->seed(AdminSeeder::class);
    $this->seed(AdminSeeder::class);

    expect(User::where('email', 'idem@inclinio.test')->count())->toBe(1);
});

it('llança excepció si falten les variables ADMIN_EMAIL o ADMIN_PASSWORD', function () {
    config(['inclinio_admin.email' => null, 'inclinio_admin.password' => null]);

    expect(fn () => $this->seed(AdminSeeder::class))
        ->toThrow(RuntimeException::class);
});
