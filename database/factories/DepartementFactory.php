<?php

namespace Database\Factories;

use App\Models\Departement;
use App\Models\Structure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Departement>
 */
class DepartementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'structure_id' => Structure::factory(),
            'code' => strtoupper(fake()->unique()->bothify('DEP-###')),
            'libelle' => 'Département '.fake()->unique()->word(),
            'description' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
