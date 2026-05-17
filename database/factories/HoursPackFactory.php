<?php

namespace Database\Factories;

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Projects\Domain\Enums\BillingMode;
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
            'billing_mode' => BillingMode::Fixed,
            'hours' => $this->faker->numberBetween(10, 120),
            'price' => Money::fromCents($this->faker->numberBetween(50000, 800000), 'EUR'),
            'hourly_rate' => null,
            'dated_on' => now()->toDateString(),
            'reason' => 'Venda inicial',
            'source_lead_id' => null,
        ];
    }

    public function hourly(int $hours = 40, int $rateCents = 2000): static
    {
        return $this->state(fn () => [
            'billing_mode' => BillingMode::Hourly,
            'hours' => $hours,
            'hourly_rate' => Money::fromCents($rateCents, 'EUR'),
            'price' => Money::fromCents($hours * $rateCents, 'EUR'),
        ]);
    }

    public function fromLead(Lead $lead): static
    {
        return $this->state(fn () => ['source_lead_id' => $lead->id]);
    }
}
