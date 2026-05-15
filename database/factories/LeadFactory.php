<?php

namespace Database\Factories;

use App\Modules\Contacts\Domain\Models\Company;
use App\Modules\Contacts\Domain\Models\Person;
use App\Modules\Crm\Domain\Enums\LeadSource;
use App\Modules\Crm\Domain\Enums\LeadStatus;
use App\Modules\Crm\Domain\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $person = Person::factory()->create();
        return [
            'person_id' => $person->id,
            'company_id' => $person->company_id,
            'status' => LeadStatus::New,
            'source' => LeadSource::Manual,
            'message' => $this->faker->sentence(),
            'tags' => [],
            'status_changed_at' => now(),
        ];
    }

    public function won(): static
    {
        return $this->state(fn () => ['status' => LeadStatus::Won]);
    }

    public function lost(): static
    {
        return $this->state(fn () => ['status' => LeadStatus::Lost]);
    }

    public function withStatus(LeadStatus $s): static
    {
        return $this->state(fn () => ['status' => $s]);
    }

    public function withTag(string $tag): static
    {
        return $this->state(fn ($attrs) => ['tags' => array_merge($attrs['tags'] ?? [], [$tag])]);
    }
}
