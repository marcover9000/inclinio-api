<?php

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/*
 * Tests del endpoint POST /password/reset.
 * Verifica que el token vàlid actualitza la contrasenya, els invàlids fallen.
 */

it('actualitza la contrasenya amb un token vàlid', function () {
    $user = User::factory()->create(['email' => 'reset@inclinio.test']);
    $token = Password::createToken($user);

    $this->postJson('/password/reset', [
        'email' => 'reset@inclinio.test',
        'token' => $token,
        'password' => 'newsecret123',
        'password_confirmation' => 'newsecret123',
    ])->assertOk();

    $user->refresh();
    expect(Hash::check('newsecret123', $user->password))->toBeTrue();
});

it('rebutja un token invàlid amb 422', function () {
    User::factory()->create(['email' => 'reset@inclinio.test']);

    $this->postJson('/password/reset', [
        'email' => 'reset@inclinio.test',
        'token' => 'invalid-token',
        'password' => 'newsecret123',
        'password_confirmation' => 'newsecret123',
    ])->assertStatus(422);
});

it('valida que la contrasenya té mín 8 caràcters i confirmació coincideix', function () {
    $user = User::factory()->create(['email' => 'short@inclinio.test']);
    $token = Password::createToken($user);

    $this->postJson('/password/reset', [
        'email' => 'short@inclinio.test',
        'token' => $token,
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['password']);

    $this->postJson('/password/reset', [
        'email' => 'short@inclinio.test',
        'token' => $token,
        'password' => 'longenough',
        'password_confirmation' => 'mismatch12',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['password']);
});

it('rebutja sense email', function () {
    $this->postJson('/password/reset', ['token' => 'x', 'password' => 'x', 'password_confirmation' => 'x'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
