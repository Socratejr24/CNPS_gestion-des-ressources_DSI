@php($dernieresNotifications = auth()->user()?->notifications()->take(8)->get() ?? collect())

<template x-if="notifDrawerOpen">
    <div>
        {{-- Overlay --}}
        <div class="app-drawer-overlay" @click="closeAllPanels()"></div>

        {{-- Drawer --}}
        <aside class="app-drawer" role="dialog" aria-label="Notifications">

            <header class="app-drawer__header">
                <h2 class="app-drawer__title">
                    <x-icon name="bell" size="md" />
                    Notifications
                </h2>
                <button type="button" class="app-drawer__close" @click="closeAllPanels()" aria-label="Fermer">
                    <x-icon name="x" size="md" />
                </button>
            </header>

            <div class="app-drawer__body">
                @forelse($dernieresNotifications as $notification)
                    @include('notifications.partials.item')
                @empty
                    <p class="app-drawer__empty">Aucune notification pour le moment.</p>
                @endforelse
            </div>

            <footer class="app-drawer__footer">
                @if(auth()->user()?->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.tout-lire') }}">
                        @csrf
                        <button type="submit" class="app-drawer__see-all">Tout marquer comme lu</button>
                    </form>
                @endif
                <a href="{{ route('notifications.index') }}" class="app-drawer__see-all">
                    Voir toutes les notifications →
                </a>
            </footer>

        </aside>
    </div>
</template>
