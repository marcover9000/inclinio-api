<?php

namespace App\Modules\Crm\Infrastructure\Listeners;

use App\Modules\Contacts\Application\Actions\MarkPersonAsClient;
use App\Modules\Crm\Domain\Events\LeadConverted;

class MarkPersonAsClientOnConversion
{
    public function __construct(private readonly MarkPersonAsClient $markPersonAsClient)
    {
    }

    public function handle(LeadConverted $event): void
    {
        $event->lead->loadMissing('person.company');
        ($this->markPersonAsClient)($event->lead->person);
    }
}
