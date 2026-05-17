<?php

namespace Database\Factories;

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Projects\Domain\Enums\ProjectStatus;
use App\Modules\Projects\Domain\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'status' => ProjectStatus::Active,
            'is_internal' => false,
            'client_company_id' => Company::factory(),
            'client_person_id' => null,
            'shadow_rate_override' => null,
            'started_at' => null,
            'due_at' => null,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn () => [
            'is_internal' => true,
            'client_company_id' => null,
            'client_person_id' => null,
        ]);
    }

    public function forCompany(Company $company): static
    {
        return $this->state(fn () => [
            'client_company_id' => $company->id,
            'client_person_id' => null,
            'is_internal' => false,
        ]);
    }

    public function forPerson(Person $person): static
    {
        return $this->state(fn () => [
            'client_company_id' => null,
            'client_person_id' => $person->id,
            'is_internal' => false,
        ]);
    }

    public function withStatus(ProjectStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
