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
                    <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="app-sidebar__label">Tableau de bord</span>
                </a>
            </div>

            {{-- Sous-section : DEMANDES --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Demandes</div>

                <a href="{{ url('/demandes/mes-demandes') }}"
                   class="app-sidebar__link {{ request()->is('demandes/mes-demandes*') ? 'app-sidebar__link--active' : '' }}">
                    <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    <span class="app-sidebar__label">Mes demandes</span>
                </a>

                <a href="{{ url('/demandes/nouvelle') }}"
                   class="app-sidebar__link {{ request()->is('demandes/nouvelle*') ? 'app-sidebar__link--active' : '' }}">
                    <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="16"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    <span class="app-sidebar__label">Nouvelle demande</span>
                </a>
            </div>

            {{-- Sous-section : RESSOURCES --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Ressources</div>

                <a href="{{ url('/applications/mes-applications') }}"
                   class="app-sidebar__link {{ request()->is('applications/mes-applications*') ? 'app-sidebar__link--active' : '' }}">
                    <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                    <span class="app-sidebar__label">Mes applications</span>
                </a>

                <a href="{{ url('/ressources') }}"
                   class="app-sidebar__link {{ request()->is('ressources*') ? 'app-sidebar__link--active' : '' }}">
                    <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                    <span class="app-sidebar__label">Ressources</span>
                </a>
            </div>

            {{-- Sous-section : COMMUNICATION --}}
            <div class="app-sidebar__sub">
                <div class="app-sidebar__sub-label">Communication</div>

                <a href="{{ url('/notifications') }}"
                   class="app-sidebar__link {{ request()->is('notifications*') ? 'app-sidebar__link--active' : '' }}">
                    <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.7 21a2 2 0 0 1-3.4 0"/>
                    </svg>
                    <span class="app-sidebar__label">Notifications</span>
                    <span class="app-sidebar__dot"></span>
                </a>
            </div>
        </div>

        {{-- ============================================================
             SECTION : TRAITEMENT
             ============================================================ --}}
        @if(user_can('voir.traitement'))
            <div class="app-sidebar__section">
                <div class="app-sidebar__section-label">Traitement</div>

                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Demandes</div>

                    <a href="{{ url('/traitement/a-traiter') }}"
                       class="app-sidebar__link {{ request()->is('traitement/a-traiter*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7m16 0v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-5m16 0h-2.6a1 1 0 0 0-.7.3l-2.4 2.4a1 1 0 0 1-.7.3h-3.2a1 1 0 0 1-.7-.3l-2.4-2.4A1 1 0 0 0 6.6 13H4"/>
                        </svg>
                        <span class="app-sidebar__label">Demandes à traiter</span>
                        <span class="app-sidebar__badge">3</span>
                    </a>

                    <a href="{{ url('/traitement/en-cours') }}"
                       class="app-sidebar__link {{ request()->is('traitement/en-cours*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 4 23 10 17 10"/>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                        </svg>
                        <span class="app-sidebar__label">Demandes en cours</span>
                    </a>

                    <a href="{{ url('/traitement/infos-complementaires') }}"
                       class="app-sidebar__link {{ request()->is('traitement/infos-complementaires*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                        </svg>
                        <span class="app-sidebar__label">Infos complémentaires</span>
                    </a>
                </div>

                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Validation</div>

                    <a href="{{ url('/traitement/validations') }}"
                       class="app-sidebar__link {{ request()->is('traitement/validations*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
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

                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Organisation</div>

                    <a href="{{ url('/admin/utilisateurs') }}"
                       class="app-sidebar__link {{ request()->is('admin/utilisateurs*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M2.5 21c0-4 3-6.5 6.5-6.5s6.5 2.5 6.5 6.5"/>
                            <circle cx="18" cy="8" r="3"/>
                            <path d="M15.5 14.5c3 .4 5 2.6 5 6.5"/>
                        </svg>
                        <span class="app-sidebar__label">Utilisateurs</span>
                    </a>

                    <a href="{{ url('/admin/structures') }}"
                       class="app-sidebar__link {{ request()->is('admin/structures*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/>
                        </svg>
                        <span class="app-sidebar__label">Structures</span>
                    </a>

                    <a href="{{ url('/admin/departements') }}"
                       class="app-sidebar__link {{ request()->is('admin/departements*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l7-4 7 4v14"/>
                            <path d="M9 9h1M9 13h1M14 9h1M14 13h1"/>
                        </svg>
                        <span class="app-sidebar__label">Départements</span>
                    </a>

                    <a href="{{ url('/admin/services') }}"
                       class="app-sidebar__link {{ request()->is('admin/services*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                        <span class="app-sidebar__label">Services</span>
                    </a>
                </div>

                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Accès</div>

                    <a href="{{ url('/admin/roles') }}"
                       class="app-sidebar__link {{ request()->is('admin/roles*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span class="app-sidebar__label">Rôles & permissions</span>
                    </a>
                </div>

                <div class="app-sidebar__sub">
                    <div class="app-sidebar__sub-label">Référentiels</div>

                    <a href="{{ url('/admin/applications') }}"
                       class="app-sidebar__link {{ request()->is('admin/applications*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        <span class="app-sidebar__label">Applications</span>
                    </a>

                    <a href="{{ url('/admin/types-ressources') }}"
                       class="app-sidebar__link {{ request()->is('admin/types-ressources*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                        <span class="app-sidebar__label">Types de ressources</span>
                    </a>

                    <a href="{{ url('/admin/ressources') }}"
                       class="app-sidebar__link {{ request()->is('admin/ressources*') ? 'app-sidebar__link--active' : '' }}">
                        <svg class="app-sidebar__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        <span class="app-sidebar__label">Ressources</span>
                    </a>
                </div>
            </div>
        @endif

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