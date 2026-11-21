@php($user = current_user())

<header class="app-topbar">
    <div class="app-topbar__left">
        <button type="button"
                class="app-burger"
                :class="{ 'app-burger--open': sidebarOpen && window.innerWidth < 1024 }"
                @click="toggleSidebar()"
                aria-label="Basculer le menu">
            <span class="app-burger__box">
                <span class="app-burger__line"></span>
                <span class="app-burger__line"></span>
                <span class="app-burger__line app-burger__line--short"></span>
            </span>
        </button>

        <h1 class="app-topbar__title">{{ $pageTitle ?? 'Tableau de bord' }}</h1>
    </div>

    <div class="app-topbar__right">

        {{-- Cloche notifications --}}
        <button type="button"
                class="app-topbar__icon-btn"
                :class="{ 'app-topbar__icon-btn--active': notifDrawerOpen }"
                @click="openNotifDrawer()"
                aria-label="Notifications">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="app-topbar__notif-dot" x-show="notifCount > 0" x-cloak></span>
        </button>

        {{-- Avatar utilisateur --}}
        <div class="app-topbar__user-wrap" x-data="{ open: false }" @click.outside="open = false">
            <button type="button"
                    class="app-topbar__user"
                    :class="{ 'app-topbar__user--active': open }"
                    @click="open = !open">
                <span class="app-topbar__user-avatar">
                    {{ $user->initiales }}
                </span>
                <span class="app-topbar__user-text">
                    <span class="app-topbar__user-name">{{ $user->prenom }} {{ $user->nom }}</span>
                    <span class="app-topbar__user-role">{{ $user->service }}</span>
                </span>
                <svg class="app-topbar__user-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="app-user-menu" x-show="open" x-cloak x-transition>
                <div class="app-user-menu__header">
                    <div class="app-user-menu__name">{{ $user->prenom }} {{ $user->nom }}</div>
                    <div class="app-user-menu__email">{{ $user->email }}</div>
                </div>
                <a href="#" class="app-user-menu__link" @click.prevent="openProfileDrawer(); open = false">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Mon profil
                </a>
                <a href="#" class="app-user-menu__link" @click.prevent="openSettingsDrawer(); open = false">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Paramètres
                </a>
                <div class="app-user-menu__separator"></div>
                <button type="button" class="app-user-menu__link app-user-menu__link--danger" @click="openLogoutModal(); open = false">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Déconnexion
                </button>
            </div>
        </div>

    </div>
</header>