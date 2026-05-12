<?php

namespace App\Modules\Contacts\Application\Actions;

use App\Modules\Contacts\Domain\Models\Company;
use Illuminate\Support\Facades\DB;

class CreateOrFindCompany
{
    /**
     * @param  array{name:string,vat?:?string,website?:?string,address?:?string,notes?:?string}  $data
     */
    public function __invoke(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $existing = Company::whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])->first();

            if ($existing) {
                $updates = [];
                foreach (['vat', 'website', 'address', 'notes'] as $field) {
                    if (!empty($data[$field]) && empty($existing->{$field})) {
                        $updates[$field] = $data[$field];
                    }
                }
                if (!empty($updates)) {
                    $existing->update($updates);
                }
                return $existing->fresh();
            }

            return Company::create([
                'name' => $data['name'],
                'vat' => $data['vat'] ?? null,
                'website' => $data['website'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
