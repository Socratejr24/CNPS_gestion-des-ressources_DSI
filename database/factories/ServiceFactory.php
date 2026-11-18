<?php

namespace Database\Factories;

use App\Models\Departement;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'departement_id' => Departement::factory(),
            'code' => strtoupper(fake()->unique()->bothify('SRV-###')),
            'libelle' => 'Service '.fake()->unique()->word(),
            'description' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
