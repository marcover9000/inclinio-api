<?php

namespace Database\Factories;

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Models\HoursPack;
use App\Modules\Projects\Domain\Models\Project;
use App\Modules\Shared\Domain\ValueObjects\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

class HoursPackFactory extends Factory
{
    protected $model = HoursPack::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'hours' => $this->faker->numberBetween(10, 120),
            'price' => Money::fromCents($this->faker->numberBetween(50000, 800000), 'EUR'),
            'dated_on' => now()->toDateString(),
            'reason' => 'Venda inicial',
            'source_lead_id' => null,
        ];
    }

    public function fromLead(Lead $lead): static
    {
        return $this->state(fn () => ['source_lead_id' => $lead->id]);
    }
}
