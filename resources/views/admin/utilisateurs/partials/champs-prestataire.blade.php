{{-- Champs du formulaire prestataire (création si $utilisateur est null, sinon modification). --}}
@php($creation = $utilisateur === null)

<div class="adm-form__grid">
    <x-form.input name="prenom" label="Prénom" :value="$utilisateur?->prenom" :error="$errors->first('prenom')" required />
    <x-form.input name="nom" label="Nom" :value="$utilisateur?->nom" :error="$errors->first('nom')" required />
    <x-form.input name="email" type="email" label="Adresse e-mail" :value="$utilisateur?->email" :error="$errors->first('email')" required />
    <x-form.input name="fonction" label="Fonction" :value="$utilisateur?->fonction" :error="$errors->first('fonction')"
                  placeholder="Ex. Consultant réseau" />
    <x-form.input name="identifiant" label="Identifiant de connexion" :value="$utilisateur?->identifiant"
                  :error="$errors->first('identifiant')" required autocomplete="off"
                  hint="Utilisé sur l'écran de connexion, onglet « Prestataire »." />
    <x-form.input name="matricule" label="Matricule / référence contrat" :value="$utilisateur?->matricule"
                  :error="$errors->first('matricule')" hint="Facultatif." />
</div>

<div class="adm-form__section">
    <h3 class="adm-form__section-title">{{ $creation ? 'Mot de passe' : 'Changer le mot de passe' }}</h3>
    <p class="adm-form__section-text">
        Au moins 8 caractères, avec des lettres et des chiffres.
        @unless($creation) Laissez vide pour conserver le mot de passe actuel. @endunless
    </p>
    <div class="adm-form__grid">
        <x-form.input name="password" type="password" label="Mot de passe" value="" :error="$errors->first('password')"
                      :required="$creation" autocomplete="new-password" />
        <x-form.input name="password_confirmation" type="password" label="Confirmation" value=""
                      :required="$creation" autocomplete="new-password" />
    </div>
</div>
