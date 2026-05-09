<?php

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Route;

/*
 * Tests del middleware EnsureRole.
 * Defineix una ruta de prova in-test per verificar el comportament.
 */

beforeEach(function () {
    Route::middleware(['auth:sanctum', 'role:admin'])
        ->get('/test/admin-only', fn () => response()->json(['ok' => true]));
});

it('permet l\'accés a usuaris amb el rol requerit', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->getJson('/test/admin-only')
        ->assertOk();
});

it('rebutja amb 403 a usuaris sense el rol requerit', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->getJson('/test/admin-only')
        ->assertForbidden();
});

it('rebutja amb 401 a usuaris no autenticats', function () {
    $this->getJson('/test/admin-only')->assertUnauthorized();
});
