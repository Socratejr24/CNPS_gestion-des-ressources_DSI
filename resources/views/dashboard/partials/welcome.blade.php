<div class="dash-welcome">
    <div class="dash-welcome__content">
        <p class="dash-welcome__date">{{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}</p>
        <h1 class="dash-welcome__title">Bonjour {{ $user->prenom }}</h1>
        <p class="dash-welcome__subtitle">
            {{ $user->service }}
            @if($user->departement !== '—') · {{ $user->departement }} @endif
        </p>
    </div>

    <div class="dash-welcome__actions">
        @if(Route::has('demandes.create'))
            <a href="{{ route('demandes.create') }}" class="dash-welcome__btn dash-welcome__btn--primary">
                <x-icon name="plus" size="sm" />
                Nouvelle demande
            </a>
        @endif
        <a href="{{ route('demandes.index') }}" class="dash-welcome__btn">
            <x-icon name="file-text" size="sm" />
            Mes demandes
        </a>
    </div>
</div>
