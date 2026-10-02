/**
 * Logique Alpine.js du Dashboard
 */
export function registerDashboard() {
    window.dashboardPage = function () {
        return {
            period: '30j',

            setPeriod(p) {
                this.period = p;
                // TODO: recharger les données selon la période
                console.log('Période sélectionnée :', p);
            },
        };
    };
}