@props([
    'action'  => null,
    'search'  => true,
    'searchPlaceholder' => 'Rechercher…',
])

<form method="GET"
      action="{{ $action ?? url()->current() }}"
      class="filters"
      {{ $attributes }}>

    @if($search)
        <div class="filters__search">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="filters__search-icon"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/>
                <line x1="21" y1="21" x2="16.6" y2="16.6"/>
            </svg>
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="{{ $searchPlaceholder }}"
                   class="filters__search-input">
        </div>
    @endif

    {{ $slot }}

    <div class="filters__actions">
        @if(request()->hasAny(['q', 'statut', 'type', 'departement']))
            <a href="{{ url()->current() }}" class="filters__reset">
                <x-icon name="x" size="xs" />
                Réinitialiser
            </a>
        @endif
    </div>

</form>