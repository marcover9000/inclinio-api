<?php

namespace App\Modules\Crm\Domain\Events;

use App\Modules\Crm\Domain\Models\Lead;
use Illuminate\Foundation\Events\Dispatchable;

class LeadConverted
{
    use Dispatchable;

    public function __construct(public readonly Lead $lead)
    {
    }
}
