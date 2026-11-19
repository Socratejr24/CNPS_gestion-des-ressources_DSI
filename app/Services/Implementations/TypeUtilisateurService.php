<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\ITypeUtilisateurService;
use App\Repositories\Interfaces\ITypeUtilisateurRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\TypeUtilisateur;

class TypeUtilisateurService implements ITypeUtilisateurService
{
    public function __construct(
        private ITypeUtilisateurRepository $typeUtilisateurRepository
    ) {}

    public function getAllTypes(): Collection
    {
        return $this->typeUtilisateurRepository->getAll();
    }

    public function getType(int $id): ?TypeUtilisateur
    {
        return $this->typeUtilisateurRepository->getById($id);
    }

    public function createType(array $data): TypeUtilisateur
    {
        // Logique métier : on pourrait vérifier que le libellé n'existe pas déjà
        return $this->typeUtilisateurRepository->create($data);
    }

    public function updateType(int $id, array $data): bool
    {
        return $this->typeUtilisateurRepository->update($id, $data);
    }

    public function deleteType(int $id): bool
    {
        return $this->typeUtilisateurRepository->delete($id);
    }

    public function toggleTypeStatus(int $id, bool $status): bool
    {
        return $this->typeUtilisateurRepository->toggleStatus($id, $status);
    }
}