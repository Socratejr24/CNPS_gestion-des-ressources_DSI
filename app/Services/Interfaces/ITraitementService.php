<?php

namespace App\Services\Interfaces;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Traitement des demandes par le receveur (SFD §8, §9.3, RG-23, RG-24, RG-27, RG-30, RG-31).
 *
 * Mise à disposition / régularisation — $lignes : [ligne_id => ['quantite_fournie' => int, 'observation' => ?string]]
 */
interface ITraitementService
{
    /**
     * Onglets de l'écran « Demandes reçues » :
     *  a-prendre : transmises à son périmètre, pas encore prises en charge ;
     *  en-cours  : prises en charge par lui et non terminées ;
     *  terminees : prises en charge par lui et dans un état final.
     */
    public function lister(User $receveur, string $onglet, array $filtres = [], int $parPage = 15): LengthAwarePaginator;

    /** @return array{a-prendre:int, en-cours:int} */
    public function compteurs(User $receveur): array;

    public function prendreEnCharge(Demande $demande, User $receveur): Demande;

    public function demanderInformations(Demande $demande, User $receveur, string $question): Demande;

    public function mettreADisposition(Demande $demande, User $receveur, array $lignes, string $date, ?string $observations): Demande;

    /** Impossibilité de traitement : état final TRAITEMENT_IMPOSSIBLE, justification obligatoire. */
    public function signalerImpossibilite(Demande $demande, User $receveur, string $justification): Demande;

    /** Régularise l'anomalie en cours et remet la demande en attente de confirmation (RG-30, RG-31). */
    public function regulariser(Demande $demande, User $receveur, array $lignes, string $explication): Demande;
}
