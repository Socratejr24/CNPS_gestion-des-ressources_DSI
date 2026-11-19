<x-mail::message>
# Mot de passe réinitialisé

Bonjour **{{ $prenom }} {{ $nom }}**,

Un administrateur a réinitialisé votre mot de passe sur l'application de gestion des ressources DSI.

Voici votre nouveau mot de passe temporaire :

<x-mail::panel>
**Mot de passe :** {{ $password }}
</x-mail::panel>

**⚠️ Important** : Ce mot de passe est **temporaire**. Vous devrez le changer lors de votre prochaine connexion.

<x-mail::button :url="config('app.url') . '/login'">
Se connecter
</x-mail::button>

Si vous n'êtes pas à l'origine de cette demande, contactez immédiatement l'administrateur.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>