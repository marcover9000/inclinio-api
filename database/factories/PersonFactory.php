<?php

namespace Database\Factories;

use App\Modules\Contacts\Domain\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->e164PhoneNumber(),
            'position' => $this->faker->jobTitle(),
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
