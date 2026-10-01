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

            confirmLogout() {
                // ⚠️ À remplacer par un POST /logout quand on aura le backend
                window.location.href = '/login';
            },

            // --- Fermer tous les panneaux ---
            closeAllPanels() {
                this.userMenuOpen = false;
                this.notifDrawerOpen = false;
                this.profileDrawerOpen = false;
                this.settingsDrawerOpen = false;
                this.logoutModalOpen = false;
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