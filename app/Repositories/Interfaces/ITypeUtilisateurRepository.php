<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\TypeUtilisateur;

interface ITypeUtilisateurRepository
{
    public function getAll(): Collection;
    public function getById(int $id): ?TypeUtilisateur;
    public function create(array $data): TypeUtilisateur;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function toggleStatus(int $id, bool $status): bool;
    public function getByLibelle(string $libelle): ?TypeUtilisateur;
}