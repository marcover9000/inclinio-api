<?php

use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Hash;

/*
 * Tests d'unitat del model User. Cobreixen:
 * - Hashing automàtic de la contrasenya en assignar-la.
 * - Helpers de rols (isAdmin, isStaff).
 * - Comportament idiomàtic d'Eloquent + Spatie HasRoles.
 */

it('hashea la contrasenya automàticament en assignar-la', function () {
    $user = User::factory()->create(['password' => 'plain-text-password']);

    expect($user->password)->not->toBe('plain-text-password');
    expect(Hash::check('plain-text-password', $user->password))->toBeTrue();
});

it('reconeix correctament un admin via isAdmin()', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isStaff())->toBeFalse();
});

it('reconeix correctament un staff via isStaff()', function () {
    $staff = User::factory()->staff()->create();

    expect($staff->isStaff())->toBeTrue();
    expect($staff->isAdmin())->toBeFalse();
});
