<x-mail::message>
# Bienvenue sur l'application CNPS DSI

Bonjour **{{ $prenom }} {{ $nom }}**,

Votre compte prestataire a été créé avec succès.

Voici vos identifiants de connexion :

<x-mail::panel>
**Identifiant :** {{ $identifiant }}  
**Mot de passe :** {{ $password }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/login'">
Se connecter
</x-mail::button>

Nous vous recommandons de changer ce mot de passe lors de votre première connexion.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>