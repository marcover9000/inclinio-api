<?php

namespace App\Modules\Crm\Domain\Exceptions;

use App\Modules\Crm\Domain\Enums\LeadStatus;
use RuntimeException;

class InvalidLeadTransition extends RuntimeException
{
    public function __construct(LeadStatus $from, LeadStatus $to)
    {
        parent::__construct(
            sprintf('Transició no vàlida: %s → %s.', $from->value, $to->value)
        );
    }
}
