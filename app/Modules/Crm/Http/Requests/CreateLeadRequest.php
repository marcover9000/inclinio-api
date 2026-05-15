<?php

namespace App\Modules\Crm\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Person picker: either reuse an existing Person via `person_id`
            // OR create a new one inline via the `person` object. Exactly
            // one path is expected; if both are sent, `person_id` wins (see
            // CreateLead action). `exists:people,id` uses the default scope
            // so soft-deleted Persons are automatically rejected.
            // `exists` on the DB table directly does NOT honor SoftDeletes
            // (the validator skips Eloquent global scopes); we add an
            // explicit `whereNull('deleted_at')` so soft-deleted Persons
            // are rejected too.
            'person_id' => [
                'required_without:person',
                'integer',
                \Illuminate\Validation\Rule::exists('people', 'id')->whereNull('deleted_at'),
            ],
            'person' => ['required_without:person_id', 'array'],
            'person.first_name' => ['required_with:person', 'string', 'max:100'],
            'person.last_name' => ['nullable', 'string', 'max:100'],
            'person.email' => ['nullable', 'email', 'max:150'],
            'person.phone' => ['nullable', 'string', 'max:32'],
            'person.position' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'array'],
            'company.name' => ['required_with:company', 'string', 'max:200'],
            'company.vat' => ['nullable', 'string', 'max:32'],
            'company.website' => ['nullable', 'url', 'max:255'],
            'company.address' => ['nullable', 'string'],
            'lead.message' => ['required', 'string', 'min:5', 'max:5000'],
            'lead.tags' => ['nullable', 'array', 'max:10'],
            'lead.tags.*' => ['string', 'max:50'],
        ];
    }
}
