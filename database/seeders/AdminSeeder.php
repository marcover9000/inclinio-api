<?php

namespace Database\Seeders;

use App\Modules\Identity\Application\Actions\CreateUser;
use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/*
 * Seeder per crear el primer usuari admin del sistema.
 * Llegeix les credencials de la config 'inclinio_admin' (que llegeix del .env).
 * Idempotent: si l'usuari ja existeix, no fa res.
 */
class AdminSeeder extends Seeder
{
    public function run(CreateUser $createUser): void
    {
        $email = config('inclinio_admin.email');
        $password = config('inclinio_admin.password');

        if (empty($email) || empty($password)) {
            throw new RuntimeException(
                "AdminSeeder necessita ADMIN_EMAIL i ADMIN_PASSWORD al .env"
            );
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} ja existeix, salto.");
            return;
        }

        $createUser(
            name: 'Administrador',
            email: $email,
            password: $password,
            role: UserRole::Admin,
        );

        $this->command?->info("Admin {$email} creat.");
    }
}
