<?php

namespace App\Modules\Identity\Infrastructure\Console;

use App\Modules\Identity\Application\Actions\CreateUser;
use App\Modules\Identity\Domain\Enums\UserRole;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

/*
 * Comanda Artisan per crear usuaris des de la línia de comandes.
 * Demana nom i contrasenya interactivament (la contrasenya és hidden).
 *
 * Exemple: php artisan user:create marc@example.com --role=admin
 */
class CreateUserCommand extends Command
{
    protected $signature = 'user:create
                            {email : Email del nou usuari}
                            {--role=staff : Rol del nou usuari (admin o staff)}';

    protected $description = "Crea un nou usuari intern (admin o staff)";

    public function handle(CreateUser $createUser): int
    {
        $email = $this->argument('email');
        $roleValue = $this->option('role');

        $role = UserRole::tryFrom($roleValue);
        if ($role === null) {
            $this->error("Rol invàlid: {$roleValue}. Valors permesos: " . implode(', ', UserRole::values()));
            return self::FAILURE;
        }

        $name = $this->ask('Nom complet');
        $password = $this->secret('Contrasenya');
        $confirmation = $this->secret('Confirmar contrasenya');

        if ($password !== $confirmation) {
            $this->error('Les contrasenyes no coincideixen.');
            return self::FAILURE;
        }

        try {
            $user = $createUser($name, $email, $password, $role);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->error("{$field}: " . implode(', ', $messages));
            }
            return self::FAILURE;
        }

        $this->info("Usuari creat: {$user->email} (rol: {$role->value})");
        return self::SUCCESS;
    }
}
