<?php

namespace App\Modules\Identity\Domain\Enums;

/*
 * Rols disponibles al sistema. Usats per Spatie Permission per a assignar
 * el rol corresponent (que es persisteix com a string a `roles` table).
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Staff = 'staff';

    /**
     * Retorna tots els rols com a array de valors string.
     * Útil per a seeders i validacions.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
