<?php

namespace App\Modules\Crm\Domain\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Proposal = 'proposal';
    case Won = 'won';
    case Lost = 'lost';

    public function canTransitionTo(LeadStatus $next): bool
    {
        return match ($this) {
            self::New => in_array($next, [self::Contacted, self::Lost], true),
            self::Contacted => in_array($next, [self::Qualified, self::Lost], true),
            self::Qualified => in_array($next, [self::Proposal, self::Lost], true),
            self::Proposal => in_array($next, [self::Won, self::Lost], true),
            self::Won, self::Lost => false,
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Won || $this === self::Lost;
    }
}
