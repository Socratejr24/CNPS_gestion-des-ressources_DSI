/**
 * Détection fiable de la perte de connexion au serveur.
 * Utilise fetch() pour vérifier si le serveur répond.
 */
(function() {
    'use strict';

    // ⚠️ Adapter selon ton contexte
    const PING_URL = '/up';           // Route Laravel health check
    const CHECK_INTERVAL = 5000;       // Vérifier toutes les 5 secondes
    const OFFLINE_PAGE = '/offline.html';

    let isOffline = false;

    /**
     * Teste si le serveur répond.
     * @returns {Promise<boolean>}
     */
    async function checkServer() {
        try {
            const response = await fetch(PING_URL, {
                method: 'HEAD',
                cache: 'no-store',
                timeout: 3000,
            });
            return response.ok;
        } catch (error) {
            return false;
        }
    }

    /**
     * Redirige vers la page offline si le serveur ne répond pas.
     */
    async function checkAndRedirect() {
        const isOnline = await checkServer();

        if (!isOnline && !isOffline) {
            isOffline = true;
            window.location.href = OFFLINE_PAGE;
        } else if (isOnline) {
            isOffline = false;
        }
    }

    // Vérifier immédiatement au chargement
    checkAndRedirect();

    // Puis vérifier périodiquement
    setInterval(checkAndRedirect, CHECK_INTERVAL);
})();