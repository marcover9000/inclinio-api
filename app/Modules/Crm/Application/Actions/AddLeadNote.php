<?php

namespace App\Modules\Crm\Application\Actions;

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Domain\Models\LeadNote;
use App\Modules\Identity\Domain\Models\User;

class AddLeadNote
{
    public function __invoke(Lead $lead, User $author, string $body): LeadNote
    {
        return $lead->notes()->create([
            'author_id' => $author->id,
            'body' => $body,
        ]);
    }
}
