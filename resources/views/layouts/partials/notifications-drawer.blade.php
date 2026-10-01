<template x-if="notifDrawerOpen">
    <div>
        {{-- Overlay --}}
        <div class="app-drawer-overlay" @click="closeAllPanels()"></div>

        {{-- Drawer --}}
        <aside class="app-drawer" role="dialog" aria-label="Notifications">

            {{-- Header --}}
            <header class="app-drawer__header">
                <h2 class="app-drawer__title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                </h2>
                <button type="button" class="app-drawer__close" @click="closeAllPanels()" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </header>

            {{-- Body --}}
            <div class="app-drawer__body">

                {{-- 🔵 Demande validée --}}
                <a href="{{ url('/demandes/DEM-001') }}" class="notif-item notif-item--unread">
                    <div class="notif-item__icon notif-item__icon--blue">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="notif-item__content">
                        <p class="notif-item__title">Demande DEM-001 validée</p>
                        <span class="notif-item__time">Il y a 10 min</span>
                    </div>
                </a>

                {{-- 🟠 Information demandée --}}
                <a href="{{ url('/demandes/DEM-002') }}" class="notif-item notif-item--unread">
                    <div class="notif-item__icon notif-item__icon--orange">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="notif-item__content">
                        <p class="notif-item__title">Information demandée</p>
                        <span class="notif-item__time">Il y a 25 min</span>
                    </div>
                </a>

                {{-- 🔵 Demande clôturée --}}
                <a href="{{ url('/demandes/DEM-003') }}" class="notif-item notif-item--unread">
                    <div class="notif-item__icon notif-item__icon--blue">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="notif-item__content">
                        <p class="notif-item__title">Demande DEM-003 clôturée</p>
                        <span class="notif-item__time">Il y a 1 h</span>
                    </div>
                </a>

            </div>

            {{-- Footer --}}
            <footer class="app-drawer__footer">
                <a href="{{ url('/notifications') }}" class="app-drawer__see-all">
                    Voir toutes les notifications →
                </a>
            </footer>

        </aside>
    </div>
</template>