/**
 * Logique Alpine.js du layout principal :
 * - Ouverture / fermeture de la sidebar
 * - Menu utilisateur (clic avatar)
 * - Drawers (notifications, profil, paramètres)
 * - Modal de déconnexion
 * - Spinner de chargement global
 */
export function registerAppLayout() {
    window.appLayout = function () {
        return {

showCurrentPwd: false,
showNewPwd: false,
showConfirmPwd: false,

  // --- Changer de mot de passe ---
changePasswordModalOpen: false,
changePasswordLoading: false,
changePasswordError: '',
changePasswordSuccess: '',
changePasswordForm: {
    current_password: '',
    password: '',
    password_confirmation: '',
},
// --- Ouvrir la modal "Changer de mot de passe" ---
openChangePasswordModal() {
    this.closeAllPanels();
    this.changePasswordForm = {
        current_password: '',
        password: '',
        password_confirmation: '',
    };
    this.changePasswordError = '';
    this.changePasswordSuccess = '';
    this.showCurrentPwd = false;
    this.showNewPwd = false;
    this.showConfirmPwd = false;
    this.changePasswordModalOpen = true;
},
// --- Soumettre le changement de mot de passe ---
// --- Soumettre le changement de mot de passe ---
async submitChangePassword() {
    this.showCurrentPwd = false;
    this.showNewPwd = false;
    this.showConfirmPwd = false;
    this.changePasswordError = '';
    this.changePasswordSuccess = '';
    this.changePasswordLoading = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/test-api/auth/change-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify(this.changePasswordForm),
        });

        const data = await response.json();

        if (!response.ok) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0];
                this.changePasswordError = Array.isArray(firstError) ? firstError[0] : firstError;
            } else {
                this.changePasswordError = data.message || 'Une erreur est survenue.';
            }
            return;
        }

        // ✅ Succès
        this.changePasswordSuccess = data.message || 'Mot de passe modifié avec succès.';
        this.changePasswordForm = {
            current_password: '',
            password: '',
            password_confirmation: '',
        };

        if (window.showToast) {
            window.showToast('success', 'Mot de passe modifié', 'Votre mot de passe a été changé avec succès.');
        }

        setTimeout(() => {
            this.changePasswordModalOpen = false;
            this.changePasswordSuccess = '';
        }, 1500);

    } catch (e) {
        console.error('❌ Erreur :', e);
        this.changePasswordError = 'Erreur réseau. Vérifiez votre connexion.';
    } finally {
        this.changePasswordLoading = false;
    }
},






            
            // --- Sidebar ---
            sidebarOpen: true,
            sidebarCollapsed: false,

            // --- Menu utilisateur ---
            userMenuOpen: false,

            // --- Drawers ---
            notifDrawerOpen: false,
            profileDrawerOpen: false,
            settingsDrawerOpen: false,

            // --- Modal déconnexion ---
            logoutModalOpen: false,

            // --- Loader ---
            loading: false,

            // --- Init ---
            init() {
                const saved = localStorage.getItem('app:sidebar');

                if (window.innerWidth < 1024) {
                    this.sidebarOpen = false;
                    this.sidebarCollapsed = false;
                } else if (saved === 'collapsed') {
                    this.sidebarOpen = true;
                    this.sidebarCollapsed = true;
                } else if (saved === 'hidden') {
                    this.sidebarOpen = false;
                    this.sidebarCollapsed = false;
                } else {
                    this.sidebarOpen = true;
                    this.sidebarCollapsed = false;
                }

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        this.sidebarOpen = true;
                    }
                });

                // Touche Échap → ferme tous les panneaux ouverts
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeAllPanels();
                    }
                });

                this.setupLinkInterceptor();

                window.addEventListener('pageshow', () => {
                    this.loading = false;
                });
            },

            // --- Sidebar ---
            toggleSidebar() {
                if (window.innerWidth < 1024) {
                    this.sidebarOpen = !this.sidebarOpen;
                    this.sidebarCollapsed = false;
                    return;
                }

                if (this.sidebarOpen && !this.sidebarCollapsed) {
                    this.sidebarCollapsed = true;
                    localStorage.setItem('app:sidebar', 'collapsed');
                } else if (this.sidebarOpen && this.sidebarCollapsed) {
                    this.sidebarOpen = false;
                    this.sidebarCollapsed = false;
                    localStorage.setItem('app:sidebar', 'hidden');
                } else {
                    this.sidebarOpen = true;
                    this.sidebarCollapsed = false;
                    localStorage.setItem('app:sidebar', 'expanded');
                }
            },

            closeSidebarOnMobile() {
                if (window.innerWidth < 1024) {
                    this.sidebarOpen = false;
                }
            },

            // --- Menu utilisateur ---
            toggleUserMenu() {
                this.userMenuOpen = !this.userMenuOpen;
            },

            closeUserMenu() {
                this.userMenuOpen = false;
            },

            // --- Drawers ---
            openNotifDrawer() {
                this.closeAllPanels();
                this.notifDrawerOpen = true;
            },

            openProfileDrawer() {
                this.closeAllPanels();
                this.profileDrawerOpen = true;
            },

            openSettingsDrawer() {
                this.closeAllPanels();
                this.settingsDrawerOpen = true;
            },

            // --- Modal déconnexion ---
            openLogoutModal() {
                this.closeAllPanels();
                this.logoutModalOpen = true;
            },

           async confirmLogout() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch('/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
        });

        // ✅ Session détruite → redirection
        window.location.href = '/login';

    } catch (e) {
        console.error('❌ Erreur logout :', e);
        // Fallback : rediriger quand même
        window.location.href = '/login';
    }
},

            // --- Fermer tous les panneaux ---
            closeAllPanels() {
                this.userMenuOpen = false;
                this.notifDrawerOpen = false;
                this.profileDrawerOpen = false;
                this.settingsDrawerOpen = false;
                this.logoutModalOpen = false;
                this.changePasswordModalOpen = false;
            },

            // --- Loader ---
            setupLinkInterceptor() {
                document.addEventListener('click', (e) => {
                    const link = e.target.closest('a');

                    if (!link) return;
                    if (link.target === '_blank') return;
                    if (link.href.startsWith('javascript:')) return;
                    if (link.href.startsWith('#')) return;
                    if (link.hasAttribute('download')) return;
                    if (link.getAttribute('href') === '#') return;

                    try {
                        const url = new URL(link.href, window.location.origin);
                        if (url.origin !== window.location.origin) return;
                        this.loading = true;
                    } catch (_) {}
                });
            },
        };
    };
}