<?php

namespace App\Modules\Projects\Domain\Exceptions;

use App\Modules\Projects\Domain\Enums\ProjectStatus;
use RuntimeException;

class InvalidProjectTransition extends RuntimeException
{
    public function __construct(ProjectStatus $from, ProjectStatus $to)
    {
        parent::__construct(
            sprintf('Transició de projecte no vàlida: %s → %s.', $from->value, $to->value)
        );
    }
}
