<?php

namespace Database\Factories;

use App\Models\Ressource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ressource>
 */
class RessourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => strtoupper(fake()->unique()->bothify('RES-###')),
            'designation' => 'Ressource '.fake()->unique()->word(),
            'categorie' => fake()->randomElement(['Matériel', 'Logiciel', 'Accès']),
            'description' => null,
            'informations_complementaires' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
