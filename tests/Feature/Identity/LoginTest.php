<?php

use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/*
 * Tests del endpoint POST /login.
 * Cobreix: credencials vàlides, invalides, validació, recordar-me, throttling.
 */

beforeEach(function () {
    RateLimiter::clear('login:127.0.0.1');
});

it('autentica un usuari amb credencials vàlides', function () {
    $user = User::factory()->staff()->create([
        'email' => 'admin@inclinio.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->getJson('/sanctum/csrf-cookie')->assertNoContent();

    $this->postJson('/login', [
        'email' => 'admin@inclinio.test',
        'password' => 'secret123',
    ])
        ->assertOk()
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'role']]);

    $this->assertAuthenticatedAs($user);
});

it('rebutja credencials incorrectes amb 422', function () {
    User::factory()->create([
        'email' => 'real@inclinio.test',
        'password' => Hash::make('correct'),
    ]);

    $this->postJson('/login', [
        'email' => 'real@inclinio.test',
        'password' => 'wrong',
    ])->assertStatus(422);

    $this->assertGuest();
});

it('valida que email i password són obligatoris', function () {
    $this->postJson('/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

it('estableix la sessió amb cookie llarga si remember és true', function () {
    User::factory()->create([
        'email' => 'remember@inclinio.test',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/login', [
        'email' => 'remember@inclinio.test',
        'password' => 'secret123',
        'remember' => true,
    ])->assertOk();

    // Laravel set una cookie 'remember_<guard>_<hash>' quan remember=true
    $cookies = collect($response->headers->getCookies());
    $rememberCookie = $cookies->first(fn ($c) => str_starts_with($c->getName(), 'remember_web_'));
    expect($rememberCookie)->not->toBeNull();
    expect($rememberCookie->getExpiresTime())->toBeGreaterThan(time() + 86400 * 7);   // > 7 dies
});

it('aplica throttling després de 5 intents fallits', function () {
    User::factory()->create(['email' => 'throttle@inclinio.test', 'password' => Hash::make('correct')]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/login', ['email' => 'throttle@inclinio.test', 'password' => 'wrong']);
    }

    $this->postJson('/login', ['email' => 'throttle@inclinio.test', 'password' => 'wrong'])
        ->assertStatus(429);
});
