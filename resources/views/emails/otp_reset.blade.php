<x-mail::message>
# Réinitialisation de mot de passe

Bonjour **{{ $prenom }} {{ $nom }}**,

Vous avez demandé la réinitialisation de votre mot de passe.

Votre code de vérification est :

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

Ce code expirera dans **15 minutes**.

Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>