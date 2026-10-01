<template x-if="settingsDrawerOpen">
    <div>
        <div class="app-drawer-overlay" @click="closeAllPanels()"></div>

        <aside class="app-drawer" role="dialog" aria-label="Paramètres">

            <header class="app-drawer__header">
                <h2 class="app-drawer__title">Paramètres</h2>
                <button type="button" class="app-drawer__close" @click="closeAllPanels()" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </header>

            <div class="app-drawer__body">

                {{-- Notifications --}}
                <div class="settings-group">
                    <h4 class="settings-group__title">Notifications</h4>

                    <div class="settings-toggle">
                        <div>
                            <div class="settings-toggle__label">Notifications par e-mail</div>
                            <div class="settings-toggle__hint">Recevoir un e-mail à chaque événement</div>
                        </div>
                        <label class="settings-switch">
                            <input type="checkbox" checked>
                            <span class="settings-switch__slider"></span>
                        </label>
                    </div>

                    <div class="settings-toggle">
                        <div>
                            <div class="settings-toggle__label">Notifications dans l'application</div>
                            <div class="settings-toggle__hint">Afficher la cloche de notifications</div>
                        </div>
                        <label class="settings-switch">
                            <input type="checkbox" checked>
                            <span class="settings-switch__slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Apparence --}}
                <div class="settings-group">
                    <h4 class="settings-group__title">Apparence</h4>

                    <div class="settings-toggle">
                        <div>
                            <div class="settings-toggle__label">Thème sombre</div>
                            <div class="settings-toggle__hint">Bientôt disponible</div>
                        </div>
                        <label class="settings-switch">
                            <input type="checkbox" disabled>
                            <span class="settings-switch__slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Sécurité --}}
                <div class="settings-group">
                    <h4 class="settings-group__title">Sécurité</h4>

                    <a href="#" class="settings-link" @click.prevent>
                        <span>Changer mon mot de passe</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cnps-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="#" class="settings-link" @click.prevent>
                        <span>Sessions actives</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-cnps-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

            </div>

        </aside>
    </div>
</template>