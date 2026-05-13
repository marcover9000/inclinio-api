<?php

namespace App\Modules\Crm\Domain\Observers;

use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;

class PersonObserver
{
    public function updated(Person $person): void
    {
        if (!$person->wasChanged('company_id')) {
            return;
        }

        Lead::query()
            ->where('person_id', $person->id)
            ->whereNotIn('status', [LeadStatus::Won->value, LeadStatus::Lost->value])
            ->update(['company_id' => $person->company_id]);
    }
}
