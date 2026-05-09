<?php

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Identity\Domain\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

/*
 * Tests del endpoint POST /password/email.
 * Verifica que envia notificació, retorna 200 sempre (no revela existència d'email),
 * valida format d'email.
 */

beforeEach(function () {
    Notification::fake();
});

it('envia notificació de reset si l\'email existeix', function () {
    $user = User::factory()->create(['email' => 'forgot@inclinio.test']);

    $this->postJson('/password/email', ['email' => 'forgot@inclinio.test'])
        ->assertOk()
        ->assertJsonPath('message', "Si l'email existeix, t'hem enviat un correu amb les instruccions.");

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('retorna 200 amb missatge genèric si l\'email no existeix (no revela)', function () {
    $this->postJson('/password/email', ['email' => 'nonexistent@inclinio.test'])
        ->assertOk()
        ->assertJsonPath('message', "Si l'email existeix, t'hem enviat un correu amb les instruccions.");

    Notification::assertNothingSent();
});

it('valida que l\'email és obligatori i format vàlid', function () {
    $this->postJson('/password/email', ['email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});
