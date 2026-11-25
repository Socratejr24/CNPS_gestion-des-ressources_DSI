{{-- Tuiles « à faire » (valideur, receveur, brouillons). Paramètre : $travail --}}
@if($travail !== [])
    <div class="dash-travail">
        @foreach($travail as $tuile)
            <a href="{{ $tuile['route'] }}" class="dash-travail__tuile {{ $tuile['valeur'] > 0 ? 'dash-travail__tuile--actif' : '' }}">
                <span class="dash-item__icon dash-item__icon--{{ $tuile['couleur'] }}"><x-icon :name="$tuile['icone']" size="sm" /></span>
                <span class="dash-travail__texte">
                    <span class="dash-travail__valeur">{{ $tuile['valeur'] }}</span>
                    <span class="dash-travail__label">{{ $tuile['label'] }}</span>
                </span>
                <x-icon name="chevron-right" size="sm" class="dash-travail__fleche" />
            </a>
        @endforeach
    </div>
@endif
