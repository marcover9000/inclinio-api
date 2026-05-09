<?php

use App\Modules\Identity\Application\Actions\CreateUser;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/*
 * Tests del Use Case CreateUser. Verifica:
 * - Creació amb dades vàlides + assignació de rol.
 * - Rejecció d'emails duplicats.
 * - Rejecció de contrasenyes massa curtes.
 * - Rol per defecte (staff) si no es passa.
 */

beforeEach(function () {
    $this->action = app(CreateUser::class);
});

it('crea un usuari amb dades vàlides i li assigna el rol indicat', function () {
    $user = ($this->action)(
        name: 'Marc',
        email: 'marc@inclinio.test',
        password: 'secret123',
        role: UserRole::Admin,
    );

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('Marc');
    expect($user->email)->toBe('marc@inclinio.test');
    expect(Hash::check('secret123', $user->password))->toBeTrue();
    expect($user->isAdmin())->toBeTrue();
});

it('assigna el rol staff per defecte si no se n\'especifica', function () {
    $user = ($this->action)(
        name: 'Anna',
        email: 'anna@inclinio.test',
        password: 'secret123',
    );

    expect($user->isStaff())->toBeTrue();
});

it('rebutja emails duplicats amb ValidationException', function () {
    User::factory()->create(['email' => 'duplicate@inclinio.test']);

    expect(fn () => ($this->action)(
        name: 'Test',
        email: 'duplicate@inclinio.test',
        password: 'secret123',
    ))->toThrow(ValidationException::class);
});

it('rebutja contrasenyes de menys de 8 caràcters', function () {
    expect(fn () => ($this->action)(
        name: 'Test',
        email: 'short@inclinio.test',
        password: 'short',
    ))->toThrow(ValidationException::class);
});
