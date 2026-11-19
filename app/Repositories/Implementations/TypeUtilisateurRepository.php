<?php

namespace App\Repositories\Implementations;

use App\Models\TypeUtilisateur;
use App\Repositories\Interfaces\ITypeUtilisateurRepository;
use Illuminate\Database\Eloquent\Collection;

class TypeUtilisateurRepository implements ITypeUtilisateurRepository
{
    public function getAll(): Collection
    {
        return TypeUtilisateur::all();
    }

    public function getById(int $id): ?TypeUtilisateur
    {
        return TypeUtilisateur::find($id);
    }

    public function getByLibelle(string $libelle): ?TypeUtilisateur
    {
        return TypeUtilisateur::where('libelle', $libelle)->first();
    }

    public function create(array $data): TypeUtilisateur
    {
        return TypeUtilisateur::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $type = TypeUtilisateur::find($id);
        return $type ? $type->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $type = TypeUtilisateur::find($id);
        return $type ? $type->delete() : false;
    }

    public function toggleStatus(int $id, bool $status): bool
    {
        $type = TypeUtilisateur::find($id);
        return $type ? $type->update(['is_active' => $status]) : false;
    }
}