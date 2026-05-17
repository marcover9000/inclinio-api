<?php

namespace App\Modules\Projects\Domain\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case Doing = 'doing';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'Per fer',
            self::Doing => 'En curs',
            self::Done => 'Feta',
        };
    }
}
