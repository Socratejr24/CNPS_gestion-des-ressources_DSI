@php
    use App\Models\Role;
    use Illuminate\Support\Facades\Route;

    $user = current_user();
    $compte = auth()->user();

    /*
     * Menu de navigation (SFD §3.3).
     * Une entrée n'est affichée que si sa route existe ET si l'utilisateur y a droit :
     * les pages pas encore développées n'apparaissent donc pas (pas de lien mort).
     */
    $sections = [
        [
            'label' => 'Demandes',
            'visible' => true,
            'items' => [
                ['route' => 'dashboard', 'actif' => 'dashboard', 'icon' => 'home', 'label' => 'Tableau de bord'],
                ['route' => 'demandes.index', 'actif' => 'demandes.index', 'icon' => 'file-text', 'label' => 'Mes demandes'],
                ['route' => 'demandes.create', 'actif' => 'demandes.create', 'icon' => 'plus-circle', 'label' => 'Nouvelle demande'],
            ],
        ],
        [
            // Responsabilités N+1 / N+2 (ce ne sont pas des rôles, cf. SFD §3.3)
            'label' => 'Validation',
            'visible' => $compte?->estValidateur() ?? false,
            'items' => [
                ['route' => 'validations.index', 'actif' => 'validations.*', 'icon' => 'check-circle', 'label' => 'Demandes à valider'],
            ],
        ],
        [
            'label' => 'Traitement',
            'visible' => $compte?->hasRole(Role::RECEVEUR) ?? false,
            'items' => [
                ['route' => 'traitement.index', 'actif' => 'traitement.*', 'icon' => 'inbox', 'label' => 'Demandes reçues'],
            ],
        ],
        [
            'label' => 'Administration',
            'visible' => $compte?->estAdministrateur() ?? false,
            'items' => [
                ['route' => 'admin.utilisateurs.index', 'actif' => 'admin.utilisateurs.*', 'icon' => 'users', 'label' => 'Utilisateurs'],
                ['route' => 'admin.organisation.index', 'actif' => 'admin.organisation.*', 'icon' => 'building', 'label' => 'Organisation'],
                ['route' => 'admin.roles.index', 'actif' => 'admin.roles.*', 'icon' => 'shield', 'label' => 'Rôles et permissions'],
                ['route' => 'admin.applications.index', 'actif' => 'admin.applications.*', 'icon' => 'monitor', 'label' => 'Applications'],
                ['route' => 'admin.ressources.index', 'actif' => 'admin.ressources.*', 'icon' => 'box', 'label' => 'Ressources'],
            ],
        ],
    ];

    foreach ($sections as $i => $section) {
        $sections[$i]['items'] = array_filter($section['items'], fn ($item) => Route::has($item['route']));
    }
    $sections = array_filter($sections, fn ($s) => $s['visible'] && $s['items'] !== []);
@endphp

<aside class="app-sidebar"
       :class="{
           'app-sidebar--collapsed': sidebarCollapsed,
           'app-sidebar--hidden-mobile': !sidebarOpen
       }">

    {{-- ===== En-tête : logo + nom ===== --}}
    <div class="app-sidebar__header">
        <img src="{{ asset('images/logo-cnps.jpeg') }}"
             alt="CNPS"
             class="app-sidebar__logo">
        <div class="app-sidebar__brand-text">
            <span class="app-sidebar__brand-title">Gestion Ressources</span>
            <span class="app-sidebar__brand-sub">DSI · CNPS</span>
        </div>
    </div>

    {{-- ===== Navigation ===== --}}
    <nav class="app-sidebar__nav" aria-label="Navigation principale">
        @foreach($sections as $section)
            <div class="app-sidebar__section">
                <div class="app-sidebar__section-label">{{ $section['label'] }}</div>

                @foreach($section['items'] as $item)
                    @php($actif = request()->routeIs($item['actif']))
                    <a href="{{ route($item['route']) }}"
                       class="app-sidebar__link {{ $actif ? 'app-sidebar__link--active' : '' }}"
                       @if($actif) aria-current="page" @endif
                       title="{{ $item['label'] }}">
                        <x-icon :name="$item['icon']" size="css" class="app-sidebar__icon" />
                        <span class="app-sidebar__label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- ===== Bloc utilisateur ===== --}}
    <div class="app-sidebar__footer">
        <div class="app-sidebar__user">
            <div class="app-sidebar__avatar">{{ $user->initiales }}</div>
            <div class="app-sidebar__user-info">
                <span class="app-sidebar__user-name">{{ $user->prenom }} {{ $user->nom }}</span>
                <span class="app-sidebar__user-role">{{ implode(' · ', $user->roles) }}</span>
            </div>
        </div>
    </div>

</aside>
