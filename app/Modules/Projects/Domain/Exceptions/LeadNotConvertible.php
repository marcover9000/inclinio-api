<?php

namespace App\Modules\Projects\Domain\Exceptions;

use App\Modules\Crm\Domain\Enums\LeadStatus;
use RuntimeException;

class LeadNotConvertible extends RuntimeException
{
    public function __construct(LeadStatus $status)
    {
        parent::__construct(
            sprintf("Només es pot convertir a projecte un lead 'won'; estat actual: %s.", $status->value)
        );
    }
}
