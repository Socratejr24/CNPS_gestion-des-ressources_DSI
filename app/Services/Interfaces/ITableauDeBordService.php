<?php

namespace App\Services\Interfaces;

use App\Models\User;

/**
 * Indicateurs du tableau de bord (SFD §14), selon les droits de l'utilisateur :
 * ses propres demandes, ou toutes les demandes pour l'administrateur.
 */
interface ITableauDeBordService
{
    /**
     * @return array{
     *   global: bool,
     *   kpis: list<array>,
     *   travail: list<array>,
     *   statuts: array{labels: list<string>, series: list<int>, colors: list<string>},
     *   evolution: array{labels: list<string>, series: array{name: string, data: list<int>}},
     *   recentes: \Illuminate\Support\Collection,
     *   notifications: \Illuminate\Support\Collection,
     * }
     */
    public function pour(User $utilisateur): array;
}
