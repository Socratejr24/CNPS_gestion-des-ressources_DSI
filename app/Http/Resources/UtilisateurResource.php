<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilisateurResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = $this->relationLoaded('service') ? $this->service : null;
        $departement = $service?->relationLoaded('departement') ? $service->departement : null;
        $structure = $departement?->relationLoaded('structure') ? $departement->structure : null;

        $resume = fn ($e) => $e ? ['id' => $e->id, 'code' => $e->code, 'libelle' => $e->libelle] : null;

        return [
            'id' => $this->id,
            'matricule' => $this->matricule,
            'identifiant' => $this->identifiant,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'nom_complet' => $this->nomComplet(),
            'email' => $this->email,
            'fonction' => $this->fonction,
            'categorie' => $this->categorie?->value,
            'categorie_label' => $this->categorie?->label(),
            'is_active' => $this->is_active,
            'affectation_en_attente' => ! $this->aUneAffectation(),
            // RG-04 : département et structure déduits du service
            'service' => $resume($service),
            'departement' => $resume($departement),
            'structure' => $resume($structure),
            'est_chef_de_service' => $service !== null && $service->chef_id === $this->id,
            'est_responsable_departement' => $departement !== null && $departement->responsable_id === $this->id,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles
                ->map(fn ($r) => ['id' => $r->id, 'libelle' => $r->libelle])
                ->values()),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
