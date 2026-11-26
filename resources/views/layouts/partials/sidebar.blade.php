@php
    use App\Models\Role;
    use Illuminate\Support\Facades\Route;

    $user = current_user();
    $compte = auth()->user();

    // Détail d'une demande : « Mes demandes » si c'est la sienne, sinon l'écran de travail (validation…).
    $demandeConsultee = request()->routeIs('demandes.show') ? request()->route('demande') : null;
    // Ouverte depuis « Toutes les demandes » (administration) : c'est cette entrée qui est active.
    $depuisAdmin = $demandeConsultee instanceof \App\Models\Demande && request('depuis') === 'admin' && ($compte?->estAdministrateur() ?? false);
    $consulteLaSienne = $demandeConsultee instanceof \App\Models\Demande && ! $depuisAdmin && $demandeConsultee->demandeur_id === $compte?->id;
    $consulteCelleDAutrui = $demandeConsultee instanceof \App\Models\Demande && ! $depuisAdmin && ! $consulteLaSienne;
    // …côté receveur dès que la demande a quitté la validation hiérarchique.
    $contexteTraitement = $consulteCelleDAutrui
        && ($demandeConsultee->receveur_id === $compte?->id
            || (($compte?->hasRole(Role::RECEVEUR) ?? false) && ! $demandeConsultee->statut->estEnValidation()));

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
                ['route' => 'demandes.index', 'actif' => request()->routeIs('demandes.index', 'demandes.edit') || $consulteLaSienne, 'icon' => 'file-text', 'label' => 'Mes demandes'],
                ['route' => 'demandes.create', 'actif' => 'demandes.create', 'icon' => 'plus-circle', 'label' => 'Nouvelle demande'],
            ],
        ],
        [
            // Responsabilités N+1 / N+2 (ce ne sont pas des rôles, cf. SFD §3.3)
            'label' => 'Validation',
            'visible' => $compte?->estValidateur() ?? false,
            'items' => [
                ['route' => 'validations.index', 'actif' => request()->routeIs('validations.*') || ($consulteCelleDAutrui && ! $contexteTraitement), 'icon' => 'check-circle', 'label' => 'Demandes à valider'],
            ],
        ],
        [
            'label' => 'Traitement',
            'visible' => $compte?->hasRole(Role::RECEVEUR) ?? false,
            'items' => [
                ['route' => 'traitement.index', 'actif' => request()->routeIs('traitement.*') || $contexteTraitement, 'icon' => 'inbox', 'label' => 'Demandes reçues'],
            ],
        ],
        [
            'label' => 'Administration',
            'visible' => $compte?->estAdministrateur() ?? false,
            'items' => [
                ['route' => 'admin.demandes.index', 'actif' => request()->routeIs('admin.demandes.*') || $depuisAdmin, 'icon' => 'file-text', 'label' => 'Toutes les demandes'],
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
                    @php($actif = is_bool($item['actif']) ? $item['actif'] : request()->routeIs($item['actif']))
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
