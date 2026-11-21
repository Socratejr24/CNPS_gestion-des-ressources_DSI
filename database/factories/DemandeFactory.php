<?php

namespace Database\Factories;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Brouillon de demande. Les champs gérés par le cycle de vie (référence, statut,
 * demandeur) sont forcés via afterMaking, hors $fillable.
 *
 * @extends Factory<Demande>
 */
class DemandeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'objet' => 'Demande '.fake()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Demande $demande) {
            $demande->reference ??= 'DEM-TEST-'.fake()->unique()->numerify('#####');
            $demande->demandeur_id ??= User::factory()->create()->id;
        });
    }

    public function pour(User $demandeur): static
    {
        return $this->afterMaking(fn (Demande $d) => $d->demandeur_id = $demandeur->id);
    }
}
