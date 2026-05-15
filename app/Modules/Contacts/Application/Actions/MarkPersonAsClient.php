<?php

namespace App\Modules\Contacts\Application\Actions;

use App\Modules\Contacts\Domain\Models\Person;
use Illuminate\Support\Facades\DB;

class MarkPersonAsClient
{
    public function __invoke(Person $person): void
    {
        DB::transaction(function () use ($person) {
            $person->promoteToClient();
            if ($person->company) {
                $person->company->promoteToClient();
            }
        });
    }
}
