<?php

namespace App\Modules\Crm\Application\Actions;

use App\Modules\Contacts\Application\Actions\CreateOrFindCompany;
use App\Modules\Contacts\Application\Actions\CreatePerson;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use Illuminate\Support\Facades\DB;

class CreateLead
{
    public function __construct(
        private readonly CreateOrFindCompany $createOrFindCompany,
        private readonly CreatePerson $createPerson,
    ) {
    }

    /**
     * @param  array{
     *   person_id?:?int,
     *   person?:?array{first_name:string,last_name?:?string,email?:?string,phone?:?string,position?:?string},
     *   company?:?array{name:string,vat?:?string,website?:?string,address?:?string},
     *   lead:array{message?:?string,tags?:?array<int,string>},
     *   source:LeadSource
     * } $data
     */
    public function __invoke(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            // Person picker: if `person_id` is set we reuse that Person and
            // intentionally IGNORE any inline `company` payload — the Person
            // already has a company_id of record, and overriding it from a
            // lead-create form would silently mutate cross-cutting state.
            // The inline `person` payload (if any) is also ignored: when
            // both arrive, `person_id` wins.
            if (!empty($data['person_id'])) {
                $person = Person::findOrFail($data['person_id']);
                $companyId = $person->company_id;
            } else {
                $companyId = null;
                if (!empty($data['company'])) {
                    $company = ($this->createOrFindCompany)($data['company']);
                    $companyId = $company->id;
                }

                $person = ($this->createPerson)([
                    ...$data['person'],
                    'company_id' => $companyId,
                ]);
            }

            return Lead::create([
                'person_id' => $person->id,
                'company_id' => $companyId,
                'status' => LeadStatus::New,
                'source' => $data['source'],
                'message' => $data['lead']['message'] ?? null,
                'tags' => $data['lead']['tags'] ?? [],
                'status_changed_at' => now(),
            ]);
        });
    }
}
