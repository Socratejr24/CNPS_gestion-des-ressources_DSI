@php
    $user = current_user();
@endphp

<header class="app-topbar">

    <div class="app-topbar__left">

        {{-- Burger animé --}}
        <button type="button"
                class="app-burger"
                :class="(sidebarCollapsed || !sidebarOpen) ? '' : 'app-burger--open'"
                @click="toggleSidebar()"
                aria-label="Basculer le menu">
            <span class="app-burger__box">
                <span class="app-burger__line"></span>
                <span class="app-burger__line app-burger__line--short"></span>
                <span class="app-burger__line"></span>
            </span>
        </button>

        <h1 class="app-topbar__title">{{ $pageTitle ?? 'Tableau de bord' }}</h1>
    </div>

    <div class="app-topbar__right">

   {{-- Cloche notifications --}}
@php $notifCount = 3; @endphp

<button type="button"
        class="app-topbar__icon-btn"
        :class="notifDrawerOpen ? 'app-topbar__icon-btn--active' : ''"
        @click="openNotifDrawer()"
        aria-label="Notifications"
        title="{{ $notifCount > 0 ? $notifCount . ' notification' . ($notifCount > 1 ? 's' : '') . ' non lue' . ($notifCount > 1 ? 's' : '') : 'Aucune notification' }}">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
    </svg>

    {{-- Pastille discrète (uniquement s'il y a des non-lues) --}}
    @if($notifCount > 0)
        <span class="app-topbar__notif-dot"></span>
    @endif
</button>


        {{-- Bloc utilisateur --}}
        <div class="app-topbar__user-wrap" @click.outside="closeUserMenu()">

            <button type="button"
                    class="app-topbar__user"
                    :class="userMenuOpen ? 'app-topbar__user--active' : ''"
                    @click="toggleUserMenu()">
                <div class="app-topbar__user-avatar">
                    {{-- Si photo : <img src="..." alt=""> --}}
                    {{-- Sinon : initiales --}}
                    {{ $user->initiales }}
                </div>
                <div class="app-topbar__user-text">
                    <span class="app-topbar__user-name">{{ $user->prenom }} {{ $user->nom }}</span>
                    <span class="app-topbar__user-role">{{ implode(' · ', $user->roles) }}</span>
                </div>
                <svg class="app-topbar__user-chevron"
                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Menu utilisateur (pop-up) --}}
            <template x-if="userMenuOpen">
                <div class="app-user-menu">

                    <div class="app-user-menu__header">
                        <div class="app-user-menu__name">
                            {{ $user->prenom }} {{ $user->nom }}
                        </div>
                        <div class="app-user-menu__roles">
                            @foreach($user->roles as $role)
                                <span class="app-user-menu__role-badge">{{ $role }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mon profil --}}
                    <button type="button" class="app-user-menu__link" @click="openProfileDrawer()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Mon profil
                    </button>

                    {{-- Paramètres --}}
                    <button type="button" class="app-user-menu__link" @click="openSettingsDrawer()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Paramètres
                    </button>

                    <div class="app-user-menu__separator"></div>

                    {{-- Déconnexion --}}
                    <button type="button" class="app-user-menu__link app-user-menu__link--danger" @click="openLogoutModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Déconnexion
                    </button>

                </div>
            </template>

        </div>

    </div>

</header>