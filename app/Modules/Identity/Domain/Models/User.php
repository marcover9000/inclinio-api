<?php

namespace App\Modules\Identity\Domain\Models;

use App\Modules\Identity\Domain\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/*
 * Agregat User. Representa un usuari intern (admin o staff) del CRM.
 * - Hashing automàtic de password via cast 'hashed' (Laravel 10+).
 * - Rols via Spatie HasRoles (assignats als seeders/comandes).
 * - Notificable per al flow de password reset.
 * - HasApiTokens per al futur d'API personal tokens (Fase 5).
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Helper de claredat: indica si l'usuari té rol admin. */
    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin->value);
    }

    /** Helper de claredat: indica si l'usuari té rol staff. */
    public function isStaff(): bool
    {
        return $this->hasRole(UserRole::Staff->value);
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
