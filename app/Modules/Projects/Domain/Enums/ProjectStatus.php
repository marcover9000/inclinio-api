<?php

namespace App\Modules\Projects\Domain\Enums;

/**
 * Cicle de vida d'un projecte. Fluid i reversible (NO màquina estricta com
 * el Lead): un projecte es pot reobrir per ampliacions. Spec §6.
 */
enum ProjectStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Done = 'done';
    case Archived = 'archived';

    public function canTransitionTo(ProjectStatus $next): bool
    {
        return match ($this) {
            self::Active => in_array($next, [self::Paused, self::Done], true),
            self::Paused => in_array($next, [self::Active, self::Done], true),
            self::Done => in_array($next, [self::Archived, self::Active], true),
            self::Archived => in_array($next, [self::Active], true),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Actiu',
            self::Paused => 'En pausa',
            self::Done => 'Acabat',
            self::Archived => 'Arxivat',
        };
    }
}
