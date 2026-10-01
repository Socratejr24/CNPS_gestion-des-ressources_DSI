<template x-if="logoutModalOpen">
    <div class="app-modal-overlay" @click.self="closeAllPanels()">
        <div class="app-modal" role="dialog" aria-labelledby="logout-title">

            <div class="app-modal__icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>

            <h2 class="app-modal__title" id="logout-title">Déconnexion</h2>
            <p class="app-modal__text">
                Voulez-vous vraiment vous déconnecter de votre session ?
            </p>

            <div class="app-modal__actions">
                <button type="button"
                        class="app-modal__btn app-modal__btn--ghost"
                        @click="closeAllPanels()">
                    Annuler
                </button>
                <button type="button"
                        class="app-modal__btn app-modal__btn--danger"
                        @click="confirmLogout()">
                    Se déconnecter
                </button>
            </div>

        </div>
    </div>
</template>