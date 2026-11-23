<?php

namespace App\Services\Interfaces;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Validation hiérarchique N+1 / N+2 (SFD §7, RG-15 à RG-22, RG-25, RG-26).
 */
interface IValidationService
{
    /** Demandes attendant la décision de l'utilisateur. Filtre : q (référence, objet, demandeur). */
    public function listerAValider(User $valideur, array $filtres = [], int $parPage = 15): LengthAwarePaginator;

    /** Demandes sur lesquelles l'utilisateur a déjà statué (validation, rejet, demande d'informations). */
    public function listerTraiteesPar(User $valideur, array $filtres = [], int $parPage = 15): LengthAwarePaginator;

    public function compterAValider(User $valideur): int;

    /** Valide l'étape en cours ; transmet au destinataire après la dernière validation (RG-22). */
    public function valider(Demande $demande, User $valideur, ?string $commentaire = null): Demande;

    /** Rejette avec motif obligatoire : état final REJETEE (RG-21, RG-32). */
    public function rejeter(Demande $demande, User $valideur, string $motif): Demande;

    /** Demande des informations complémentaires au demandeur (SFD §10). */
    public function demanderInformations(Demande $demande, User $valideur, string $question): Demande;
}
