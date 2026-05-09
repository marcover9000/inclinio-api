<?php

namespace Database\Factories;

use App\Modules\Identity\Domain\Enums\UserRole;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
 * Factory del User model. Usada per a tests i seeders.
 * Inclou estats admin() i staff() per a assignar rols ràpidament.
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    /** Crea un usuari amb rol admin assignat (requereix que el rol existeixi). */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole(UserRole::Admin->value);
        });
    }

    /** Crea un usuari amb rol staff assignat. */
    public function staff(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole(UserRole::Staff->value);
        });
    }
}
