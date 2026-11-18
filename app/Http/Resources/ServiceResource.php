<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'libelle' => $this->libelle,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'departement' => new DepartementResource($this->whenLoaded('departement')),
            'chef' => new UtilisateurResumeResource($this->whenLoaded('chef')),
            'utilisateurs_count' => $this->whenCounted('utilisateurs'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
