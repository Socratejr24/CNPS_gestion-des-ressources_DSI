<div class="dash-welcome">
    <div class="dash-welcome__content">
        <h1 class="dash-welcome__title">Bonjour {{ $user->prenom }} </h1>
        <p class="dash-welcome__subtitle">
            Voici un aperçu de votre activité du {{ now()->format('d/m/Y') }}
        </p>
    </div>
    <div>
        <a href="{{ url('/demandes/nouvelle') }}" class="btn btn--accent">
            + Nouvelle demande
        </a>
    </div>
</div>