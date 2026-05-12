<?php

namespace App\Modules\Contacts\Application\Actions;

use App\Modules\Contacts\Domain\Models\Person;
use Illuminate\Validation\ValidationException;

class CreatePerson
{
    /**
     * @param  array{first_name:string,last_name?:?string,email?:?string,phone?:?string,position?:?string,company_id?:?int}  $data
     */
    public function __invoke(array $data): Person
    {
        if (!empty($data['email'])) {
            $existing = Person::whereNull('deleted_at')
                ->where('email', $data['email'])
                ->exists();
            if ($existing) {
                throw ValidationException::withMessages([
                    'email' => 'Aquesta adreça electrònica ja està registrada.',
                ]);
            }
        }

        return Person::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'company_id' => $data['company_id'] ?? null,
        ]);
    }
}
