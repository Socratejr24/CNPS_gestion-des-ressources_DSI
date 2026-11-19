<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'identifiant' => $this->identifiant,
            'matricule' => $this->matricule,
            'fonction' => $this->fonction,
            'telephone' => $this->telephone,
             'photo_url' => $this->photo_url,  
            'is_active' => $this->is_active,
            'is_ldap' => $this->is_ldap,
            'must_change_password' => $this->must_change_password,
            'type_utilisateur_id' => $this->type_utilisateur_id,

// Relation (chargée avec whenLoaded pour éviter N+1)
            'type_utilisateur' => $this->whenLoaded('typeUtilisateur', fn() => [
                'id'      => $this->typeUtilisateur->id,
                'libelle' => $this->typeUtilisateur->libelle,
            ]),

            'roles' => \App\Http\Resources\RoleResource::collection($this->whenLoaded('roles')),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),


        ];
    }
}