<?php

namespace App\Services\Auth;

use App\Enums\CategorieUtilisateur;
use App\Models\Role;
use App\Models\User;
use App\Services\Ldap\AnnuaireIndisponibleException;
use App\Services\Ldap\AnnuaireLdap;
use App\Services\Ldap\ProfilLdap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Vérifie les identifiants et renvoie le profil local (SFD §3.1).
 *
 * - Agents CNPS : annuaire LDAP, création du profil local au premier accès (RG-07, RG-09, RG-10).
 * - Prestataires : compte local géré par l'administrateur (RG-08).
 *
 * La session (Auth::login) est ouverte par le contrôleur.
 */
class AuthentificationService
{
    public const MESSAGE_AGENT = 'Matricule ou mot de passe incorrect.';

    public const MESSAGE_PRESTATAIRE = 'Adresse e-mail ou mot de passe incorrect.';

    public const MESSAGE_DESACTIVE = 'Votre compte est désactivé. Contactez l\'administrateur de l\'application.';

    public const MESSAGE_ANNUAIRE = 'L\'annuaire CNPS est momentanément indisponible. Réessayez dans quelques instants.';

    public function __construct(private AnnuaireLdap $annuaire) {}

    /**
     * @throws EchecAuthentificationException
     */
    public function authentifier(CategorieUtilisateur $categorie, string $identifiant, string $motDePasse): User
    {
        $identifiant = trim($identifiant);

        $utilisateur = $categorie === CategorieUtilisateur::PRESTATAIRE
            ? $this->authentifierPrestataire($identifiant, $motDePasse)
            : $this->authentifierAgent($identifiant, $motDePasse);

        if (! $utilisateur->is_active) {
            throw new EchecAuthentificationException(self::MESSAGE_DESACTIVE);
        }

        return $utilisateur;
    }

    // ------------------------------------------------------------------
    // Prestataires (comptes locaux)
    // ------------------------------------------------------------------

    private function authentifierPrestataire(string $email, string $motDePasse): User
    {
        $utilisateur = User::where('email', $email)
            ->where('categorie', CategorieUtilisateur::PRESTATAIRE)
            ->first();

        // Hash::check même si l'utilisateur n'existe pas : temps de réponse constant.
        $hash = $utilisateur?->password ?? '$2y$12$'.str_repeat('a', 53);
        if (! Hash::check($motDePasse, $hash) || $utilisateur === null) {
            throw new EchecAuthentificationException(self::MESSAGE_PRESTATAIRE);
        }

        return $utilisateur;
    }

    // ------------------------------------------------------------------
    // Agents CNPS (LDAP)
    // ------------------------------------------------------------------

    private function authentifierAgent(string $identifiant, string $motDePasse): User
    {
        // 1. Vérification des identifiants auprès de l'annuaire.
        try {
            $profil = $this->annuaire->authentifier($identifiant, $motDePasse);
        } catch (AnnuaireIndisponibleException $e) {
            Log::error('LDAP indisponible', ['message' => $e->getMessage()]);
            throw new EchecAuthentificationException(self::MESSAGE_ANNUAIRE, previous: $e);
        }

        if ($profil === null) {
            throw new EchecAuthentificationException(self::MESSAGE_AGENT);
        }

        // 2-4. Recherche ou création du profil local.
        return DB::transaction(fn () => $this->synchroniserProfil($identifiant, $profil));
    }

    private function synchroniserProfil(string $identifiant, ProfilLdap $profil): User
    {
        $utilisateur = User::withTrashed()
            ->where('matricule', $profil->matricule)
            ->orWhere('identifiant', $identifiant)
            ->lockForUpdate()
            ->first();

        // Profil supprimé par l'administrateur : pas de recréation silencieuse.
        if ($utilisateur?->trashed()) {
            throw new EchecAuthentificationException(self::MESSAGE_DESACTIVE);
        }

        // Un compte local prestataire ne peut pas être « pris » par un login LDAP.
        if ($utilisateur && $utilisateur->categorie !== CategorieUtilisateur::AGENT) {
            Log::warning('Connexion LDAP sur un compte non-agent refusée', ['utilisateur_id' => $utilisateur->id]);
            throw new EchecAuthentificationException(self::MESSAGE_AGENT);
        }

        $donnees = [
            'matricule' => $profil->matricule,
            'identifiant' => $identifiant,
            'nom' => $profil->nom,
            'prenom' => $profil->prenom,
            'fonction' => $profil->fonction,
        ];

        // L'email est unique : on ne l'écrase pas s'il appartient déjà à un autre compte.
        if ($profil->email && ! User::withTrashed()
            ->where('email', $profil->email)
            ->when($utilisateur, fn ($q) => $q->whereKeyNot($utilisateur->id))
            ->exists()) {
            $donnees['email'] = $profil->email;
        }

        if ($utilisateur) {
            // Les informations de l'annuaire font foi : mise à jour à chaque connexion.
            $utilisateur->update($donnees);

            return $utilisateur;
        }

        // 3. Création automatique du profil local (RG-09).
        $utilisateur = User::create($donnees + [
            'email' => $donnees['email'] ?? $profil->matricule.'@ldap.invalid',
            'categorie' => CategorieUtilisateur::AGENT,
            'password' => null,
            'is_active' => true,
        ]);

        // 4. Rôle Demandeur par défaut (RG-10).
        $demandeur = Role::where('libelle', Role::DEMANDEUR)->where('is_active', true)->first();
        if ($demandeur) {
            $utilisateur->roles()->attach($demandeur->id);
        } else {
            Log::error('Rôle Demandeur introuvable : nouveau profil LDAP créé sans rôle', ['utilisateur_id' => $utilisateur->id]);
        }

        // 5. Pas de service : l'affectation est en attente (RG-11), gérée par le middleware.
        return $utilisateur;
    }
}
