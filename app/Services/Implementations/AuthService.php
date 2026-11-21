<?php

namespace App\Services\Implementations;
use App\Http\Requests\ChangePasswordRequest;
use App\Helpers\AuthHelper;
use App\Mail\OtpResetPasswordMail;
use App\Mail\PrestataireCredentialsMail;
use App\Repositories\Interfaces\IAuthRepository;
use App\Repositories\Interfaces\IOtpRepository;
use App\Models\Role;
use App\Models\TypeUtilisateur;
use App\Models\User;
use App\Services\Interfaces\IAuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthService implements IAuthService
{
    public function __construct(
        private IAuthRepository $authRepository,
        private IOtpRepository $otpRepository
    ) {}

    /**
     * Login Agent CNPS via LDAP.
     * L'agent fournit UNIQUEMENT son matricule et mot de passe.
     * Toutes les autres infos viennent de l'annuaire LDAP.
     */
    public function loginAgentLDAP(string $matricule, string $password): array
    {
        // 🚧 MOCK LDAP - à remplacer par LdapRecord plus tard
        if ($password !== 'password123') {
            throw ValidationException::withMessages(['matricule' => 'Identifiants LDAP invalides.']);
        }

        // 🚧 MOCK LDAP - données simulées de l'annuaire
        // En vrai, on les récupérera via LdapRecord::query()->where('matricule', $matricule)->first()
        $ldapData = [
            'nom'       => 'Kouassi',
            'prenom'    => 'Jean',
            'email'     => strtolower($matricule) . '@cnps.ci',
            'fonction'  => 'Développeur',
            'telephone' => '+225 27 20 00 00 00',
            'matricule' => $matricule,
            // ⚠️ Pas de 'structure' : tous les agents sont à la DSI
        ];

        $user = $this->authRepository->findByMatricule($matricule);

        if (!$user) {
            // Première connexion → on crée le profil avec les données LDAP
            $typeAgent     = TypeUtilisateur::where('libelle', 'Agent CNPS')->first();
            $roleDemandeur = Role::where('libelle', 'Demandeur')->first();

            $user = $this->authRepository->createUser([
                'matricule'           => $ldapData['matricule'],
                'nom'                 => $ldapData['nom'],
                'prenom'              => $ldapData['prenom'],
                'email'               => $ldapData['email'],
                'fonction'            => $ldapData['fonction'],
                'telephone'           => $ldapData['telephone'],
                'identifiant'         => null, // ✅ Les agents n'ont pas d'identifiant
                'password'            => null, // ✅ Pas de mot de passe local
                'is_ldap'             => true,
                'is_active'           => true,
                'type_utilisateur_id' => $typeAgent?->id,
            ]);

            if ($roleDemandeur) {
                $user->roles()->attach($roleDemandeur->id);
            }
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages(['matricule' => 'Compte désactivé.']);
        }

        $this->authRepository->revokeTokens($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Login Prestataire avec identifiant + mot de passe.
     */
    public function loginPrestataire(string $identifiant, string $password): array
    {
        $user = $this->authRepository->findByIdentifiant($identifiant);

        if (!$user || !$user->password || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['identifiant' => 'Identifiants invalides.']);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages(['identifiant' => 'Compte désactivé.']);
        }

        $this->authRepository->revokeTokens($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Créer un prestataire (par l'admin).
     * L'identifiant et le mot de passe sont GÉNÉRÉS AUTOMATIQUEMENT.
     * Ils sont envoyés par email au prestataire.
     */
    public function createPrestataire(array $data): User
    {
        $typePrestataire = TypeUtilisateur::where('libelle', 'Prestataire')->first();

        // ✅ Génération auto via AuthHelper
        $identifiant   = AuthHelper::generateIdentifiant($data['prenom'], $data['nom']);
        $passwordPlain = AuthHelper::generatePassword();

        $data['identifiant']         = $identifiant;
        $data['password']            = $passwordPlain; 
        $data['is_ldap']             = false;
        $data['is_active']           = true;
        $data['type_utilisateur_id'] = $typePrestataire?->id;

        // L'email n'est pas obligatoire
        if (empty($data['email'])) {
            unset($data['email']);
        }

        $prestataire = $this->authRepository->createUser($data);

        $roleDemandeur = Role::where('libelle', 'Demandeur')->first();
        if ($roleDemandeur) {
            $prestataire->roles()->attach($roleDemandeur->id);
        }

        //  Envoi email avec identifiant + mot de passe
        if (!empty($prestataire->email)) {
            Mail::to($prestataire->email)->send(
                new PrestataireCredentialsMail(
                    nom:          $prestataire->nom,
                    prenom:       $prestataire->prenom,
                    identifiant:  $identifiant,
                    passwordPlain: $passwordPlain,
                )
            );
        }

        return $prestataire;
    }

    public function logout(User $user): void
    {
        $this->authRepository->revokeTokens($user);
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            // Sécurité : ne pas révéler si l'email existe
            return;
        }

        $otp = AuthHelper::generateOtp();
        $this->otpRepository->createOtp($email, $otp);

        // Envoi du mail avec template Blade
        Mail::to($email)->send(
            new OtpResetPasswordMail(
                nom:    $user->nom,
                prenom: $user->prenom,
                otp:    $otp,
            )
        );
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        return $this->otpRepository->findValidOtp($email, $otp) !== null;
    }

    public function resetPassword(string $email, string $otp, string $newPassword): bool
    {
        if (!$this->verifyOtp($email, $otp)) {
            throw ValidationException::withMessages(['otp' => 'Code invalide ou expiré.']);
        }

        $user = $this->authRepository->findByEmail($email);
        if (!$user) {
            throw ValidationException::withMessages(['email' => 'Utilisateur introuvable.']);
        }

        $this->authRepository->updatePassword($user, $newPassword);
        $this->otpRepository->deleteOtpsForEmail($email);

        return true;
    }
public function changePassword(User $user, string $currentPassword, string $newPassword): bool
{
    // 1. Vérifier l'ancien mot de passe
    if (!Hash::check($currentPassword, $user->password)) {
        throw ValidationException::withMessages([
            'current_password' => 'Le mot de passe actuel est incorrect.',
        ]);
    }

    // 2. Empêcher de remettre le même mot de passe
    if (Hash::check($newPassword, $user->password)) {
        throw ValidationException::withMessages([
            'password' => 'Le nouveau mot de passe doit être différent de l\'ancien.',
        ]);
    }

    // 3. Mettre à jour (le cast 'hashed' hash automatiquement)
    return $this->authRepository->updatePassword($user, $newPassword);
}

}