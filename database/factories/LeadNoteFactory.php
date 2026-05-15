<?php

namespace Database\Factories;

use App\Modules\Crm\Domain\Models\Lead;
use App\Modules\Crm\Domain\Models\LeadNote;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadNoteFactory extends Factory
{
    protected $model = LeadNote::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'author_id' => User::factory(),
            'body' => $this->faker->paragraph(),
        ];
    }
}
