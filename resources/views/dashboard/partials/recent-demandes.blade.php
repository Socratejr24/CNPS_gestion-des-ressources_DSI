<div class="dash-block">
    {{-- Header --}}
    <div class="dash-block__header">
        <h3 class="dash-block__title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="16" y1="13" x2="8" y2="13" stroke-linecap="round"/>
                <line x1="16" y1="17" x2="8" y2="17" stroke-linecap="round"/>
            </svg>
            Dernières demandes
        </h3>
    </div>

    {{-- Body --}}
    <div class="dash-block__body">
        @if($recentesDemandes->isEmpty())
            <div class="dash-empty">
                <p class="dash-empty__text">Aucune demande pour l'instant.</p>
            </div>
        @else
            @foreach($recentesDemandes as $demande)
                @php
                    $iconColors = [
                        'attente'  => 'orange',
                        'cours'    => 'blue',
                        'validee'  => 'green',
                        'cloturee' => 'gray',
                        'rejetee'  => 'red',
                    ];
                    $color = $iconColors[$demande->statut] ?? 'gray';
                @endphp
                <a href="{{ url('/demandes/' . $demande->reference) }}" class="dash-item">
                    <div class="dash-item__icon dash-item__icon--{{ $color }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="dash-item__content">
                        <p class="dash-item__title">{{ $demande->objet }}</p>
                        <div class="dash-item__meta">
                            <span>{{ $demande->reference }}</span>
                            <span>·</span>
                            <span>{{ \Carbon\Carbon::parse($demande->date)->format('d/m') }}</span>
                        </div>
                    </div>
                    <span class="dash-status dash-status--{{ $demande->statut }}">
                        {{ $demande->statut_label }}
                    </span>
                </a>
            @endforeach
        @endif
    </div>

    {{-- Footer --}}
    <div class="dash-block__footer">
        <a href="{{ url('/demandes/mes-demandes') }}" class="dash-block__view-all">
            Voir toutes mes demandes
        </a>
    </div>
</div>