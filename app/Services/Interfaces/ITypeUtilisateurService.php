<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\TypeUtilisateur;

interface ITypeUtilisateurService
{
    public function getAllTypes(): Collection;
    public function getType(int $id): ?TypeUtilisateur;
    public function createType(array $data): TypeUtilisateur;
    public function updateType(int $id, array $data): bool;
    public function deleteType(int $id): bool;
    public function toggleTypeStatus(int $id, bool $status): bool;
}