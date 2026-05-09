<?php

namespace App\Modules\Identity\Application\Actions;

use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Support\Facades\Validator;

/*
 * Use Case: crea un nou usuari del sistema.
 * Valida les dades, persisteix el model i assigna el rol indicat.
 *
 * Llançat des de:
 * - AdminSeeder (primer admin)
 * - CreateUserCommand (per a col·laboradors via CLI)
 * - Futures pantalles d'invitació (no implementades a Fase 1)
 */
class CreateUser
{
    public function __invoke(
        string $name,
        string $email,
        string $password,
        UserRole $role = UserRole::Staff,
    ): User {
        Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ],
        )->validate();

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,    // Cast 'hashed' s'aplica automàticament
        ]);

        $user->assignRole($role->value);

        return $user;
    }
}
