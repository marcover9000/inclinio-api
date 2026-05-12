<?php

namespace Database\Factories;

use App\Modules\Contacts\Domain\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'vat' => strtoupper($this->faker->bothify('?#########')),
            'website' => $this->faker->url(),
            'address' => $this->faker->address(),
            'notes' => null,
            'is_client' => false,
            'became_client_at' => null,
        ];
    }

    public function client(): static
    {
        return $this->state(fn () => [
            'is_client' => true,
            'became_client_at' => now(),
        ]);
    }
}
