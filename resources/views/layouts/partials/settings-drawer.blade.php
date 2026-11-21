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
                            <div class="settings-toggle__hint">Basculez vers l'interface sombre</div>
                        </div>
                        <label class="settings-switch">
                            {{--
                              x-model="isDark" : la variable DOIT venir du composant parent
                              (appLayout()), pas d'un x-data local. Sinon chaque toggle
                              crée un scope isolé et le thème ne se propage pas.
                            --}}
                            <input type="checkbox" x-model="isDark" @change="applyTheme()">
                            <span class="settings-switch__slider"></span>
                        </label>
                    </div>
                </div>

                {{-- Sécurité --}}
                <div class="settings-group">
                    <h4 class="settings-group__title">Sécurité</h4>

                   <a href="#" class="settings-link" @click.prevent="openChangePasswordModal()">
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


{{-- ============================================
     Modal : Changer de mot de passe
     ============================================ --}}
<template x-if="changePasswordModalOpen">
    <div class="app-modal-overlay" @click.self="closeAllPanels()">
        <div class="app-modal app-modal--lg" role="dialog" aria-labelledby="change-pwd-title">

            {{-- Header --}}
            <header class="app-modal__header">
                <h2 class="app-modal__title" id="change-pwd-title">
                    Changer mon mot de passe
                </h2>
                <button type="button"
                        class="app-modal__close"
                        @click="closeAllPanels()"
                        aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                         fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2"
                         style="width:20px;height:20px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </header>

            {{-- Body --}}
            <div class="app-modal__body">

                {{-- Erreur --}}
                <template x-if="changePasswordError">
                    <div class="login-error" role="alert" style="margin-bottom: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2"
                             style="width:16px;height:16px;flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="changePasswordError"></span>
                    </div>
                </template>

                {{-- Succès --}}
                <template x-if="changePasswordSuccess">
                    <div class="login-error" role="alert"
                         style="background:#e6f7eb;color:#088e39;border-left-color:#088e39;margin-bottom: 1rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2"
                             style="width:16px;height:16px;flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="changePasswordSuccess"></span>
                    </div>
                </template>

                {{-- Formulaire --}}
                <form @submit.prevent="submitChangePassword">

                    {{-- Ancien mot de passe --}}
                    <div class="login-field">
                        <label for="current_password" class="login-label">
                            Mot de passe actuel
                        </label>
                        <div class="login-password-wrap">
                            <input :type="showCurrentPwd ? 'text' : 'password'"
                                   id="current_password"
                                   name="current_password"
                                   x-model="changePasswordForm.current_password"
                                   class="login-input"
                                   placeholder="••••••••"
                                   required>
                            <button type="button"
                                    class="login-eye-btn"
                                    @click="showCurrentPwd = !showCurrentPwd"
                                    :aria-label="showCurrentPwd ? 'Masquer' : 'Afficher'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                     fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.8"
                                     style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Nouveau mot de passe --}}
                    <div class="login-field">
                        <label for="new_password" class="login-label">
                            Nouveau mot de passe
                        </label>
                        <div class="login-password-wrap">
                            <input :type="showNewPwd ? 'text' : 'password'"
                                   id="new_password"
                                   name="new_password"
                                   x-model="changePasswordForm.password"
                                   class="login-input"
                                   placeholder="••••••••"
                                   minlength="8"
                                   required>
                            <button type="button"
                                    class="login-eye-btn"
                                    @click="showNewPwd = !showNewPwd"
                                    :aria-label="showNewPwd ? 'Masquer' : 'Afficher'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                     fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.8"
                                     style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="login-hint" style="margin-top: 0.4rem;">
                            Minimum 8 caractères, avec lettres, chiffres et symboles.
                        </p>
                    </div>

                    {{-- Confirmation --}}
                    <div class="login-field">
                        <label for="password_confirmation" class="login-label">
                            Confirmer le nouveau mot de passe
                        </label>
                        <div class="login-password-wrap">
                            <input :type="showConfirmPwd ? 'text' : 'password'"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   x-model="changePasswordForm.password_confirmation"
                                   class="login-input"
                                   placeholder="••••••••"
                                   required>
                            <button type="button"
                                    class="login-eye-btn"
                                    @click="showConfirmPwd = !showConfirmPwd"
                                    :aria-label="showConfirmPwd ? 'Masquer' : 'Afficher'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                     fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.8"
                                     style="width:20px;height:20px;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="app-modal__actions" style="margin-top: 1.5rem;">
                        <button type="button"
                                class="btn btn--ghost"
                                @click="closeAllPanels()">
                            Annuler
                        </button>
                        <button type="submit"
                                class="login-submit"
                                style="width: auto; padding: 0.6rem 1.2rem; margin: 0;"
                                :disabled="changePasswordLoading">
                            <span x-show="changePasswordLoading" class="login-submit__spinner"></span>
                            <span x-text="changePasswordLoading ? 'Enregistrement...' : 'Enregistrer'"></span>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</template>