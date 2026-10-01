@php
    $user = current_user();
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
    <nav class="app-sidebar__nav">

        {{-- ============================================================
             SECTION : PRINCIPAL
             ============================================================ --}}
        <div class="app-sidebar__section">
            <div class="app-sidebar__section-label">Principal</div>

            {{-- Sous-section : ACCUEIL --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Accueil</div>

                <a href="{{ url('/dashboard') }}"
                   class="app-sidebar__link {{ request()->is('dashboard') ? 'app-sidebar__link--active' : '' }}">
                    <i class="fa-solid fa-house app-sidebar__icon"></i>
                    <span class="app-sidebar__label">Tableau de bord</span>
                </a>
            </div>

            {{-- Sous-section : DEMANDES --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Demandes</div>

                <a href="{{ url('/demandes/mes-demandes') }}"
                   class="app-sidebar__link {{ request()->is('demandes/mes-demandes*') ? 'app-sidebar__link--active' : '' }}">
                    <i class="fa-solid fa-file-lines app-sidebar__icon"></i>
                    <span class="app-sidebar__label">Mes demandes</span>
                </a>

                <a href="{{ url('/demandes/nouvelle') }}"
                   class="app-sidebar__link {{ request()->is('demandes/nouvelle*') ? 'app-sidebar__link--active' : '' }}">
                    <i class="fa-solid fa-circle-plus app-sidebar__icon"></i>
                    <span class="app-sidebar__label">Nouvelle demande</span>
                </a>
            </div>

            {{-- Sous-section : RESSOURCES --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Ressources</div>

                <a href="{{ url('/applications/mes-applications') }}"
                   class="app-sidebar__link {{ request()->is('applications/mes-applications*') ? 'app-sidebar__link--active' : '' }}">
                    <i class="fa-solid fa-laptop-code app-sidebar__icon"></i>
                    <span class="app-sidebar__label">Mes applications</span>
                </a>

                <a href="{{ url('/ressources') }}"
                   class="app-sidebar__link {{ request()->is('ressources*') ? 'app-sidebar__link--active' : '' }}">
                    <i class="fa-solid fa-box app-sidebar__icon"></i>
                    <span class="app-sidebar__label">Ressources</span>
                </a>
            </div>

            {{-- Sous-section : COMMUNICATION --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Communication</div>

                <a href="{{ url('/notifications') }}"
                   class="app-sidebar__link {{ request()->is('notifications*') ? 'app-sidebar__link--active' : '' }}">
                    <i class="fa-solid fa-bell app-sidebar__icon"></i>
                    <span class="app-sidebar__label">Notifications</span>
                    <span class="app-sidebar__dot"></span>
                </a>
            </div>
        </div>

        {{-- ============================================================
             SECTION : TRAITEMENT (Receveur / Valideur)
             ============================================================ --}}
        @if(user_can('voir.traitement'))
            <div class="app-sidebar__section">
                <div class="app-sidebar__section-label">Traitement</div>

                {{-- Sous-section : DEMANDES --}}
                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Demandes</div>

                    <a href="{{ url('/traitement/a-traiter') }}"
                       class="app-sidebar__link {{ request()->is('traitement/a-traiter*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-inbox app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Demandes à traiter</span>
                        <span class="app-sidebar__badge">3</span>
                    </a>

                    <a href="{{ url('/traitement/en-cours') }}"
                       class="app-sidebar__link {{ request()->is('traitement/en-cours*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-rotate app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Demandes en cours</span>
                    </a>

                    <a href="{{ url('/traitement/infos-complementaires') }}"
                       class="app-sidebar__link {{ request()->is('traitement/infos-complementaires*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-comment-dots app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Infos complémentaires</span>
                    </a>
                </div>

                {{-- Sous-section : VALIDATION --}}
                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Validation</div>

                    <a href="{{ url('/traitement/validations') }}"
                       class="app-sidebar__link {{ request()->is('traitement/validations*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-circle-check app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Mes validations</span>
                        <span class="app-sidebar__badge">2</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- ============================================================
             SECTION : ADMINISTRATION
             ============================================================ --}}
        @if(user_can('voir.administration'))
            <div class="app-sidebar__section">
                <div class="app-sidebar__section-label">Administration</div>

                {{-- Sous-section : ORGANISATION --}}
                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Organisation</div>

                    <a href="{{ url('/admin/utilisateurs') }}"
                       class="app-sidebar__link {{ request()->is('admin/utilisateurs*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-users app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Utilisateurs</span>
                    </a>

                    <a href="{{ url('/admin/structures') }}"
                       class="app-sidebar__link {{ request()->is('admin/structures*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-building app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Structures</span>
                    </a>

                    <a href="{{ url('/admin/departements') }}"
                       class="app-sidebar__link {{ request()->is('admin/departements*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-building-columns app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Départements</span>
                    </a>

                    <a href="{{ url('/admin/services') }}"
                       class="app-sidebar__link {{ request()->is('admin/services*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-sitemap app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Services</span>
                    </a>
                </div>

                {{-- Sous-section : ACCÈS --}}
                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Accès</div>

                    <a href="{{ url('/admin/roles') }}"
                       class="app-sidebar__link {{ request()->is('admin/roles*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-shield-halved app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Rôles & permissions</span>
                    </a>
                </div>

                {{-- Sous-section : RÉFÉRENTIELS --}}
                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Référentiels</div>

                    <a href="{{ url('/admin/applications') }}"
                       class="app-sidebar__link {{ request()->is('admin/applications*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-laptop app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Applications</span>
                    </a>

                    <a href="{{ url('/admin/types-ressources') }}"
                       class="app-sidebar__link {{ request()->is('admin/types-ressources*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-tags app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Types de ressources</span>
                    </a>

                    <a href="{{ url('/admin/ressources') }}"
                       class="app-sidebar__link {{ request()->is('admin/ressources*') ? 'app-sidebar__link--active' : '' }}">
                        <i class="fa-solid fa-boxes-stacked app-sidebar__icon"></i>
                        <span class="app-sidebar__label">Ressources</span>
                    </a>
                </div>
            </div>
        @endif

    </nav>

    {{-- ===== Bloc utilisateur (non cliquable) ===== --}}
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