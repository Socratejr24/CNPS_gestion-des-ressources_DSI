/**
 * Helper pour ouvrir/fermer les modales via événements window
 */
export function registerModals() {
    window.openModal = function (id) {
        window.dispatchEvent(new CustomEvent(`open-${id}`));
    };

    window.closeModal = function (id) {
        window.dispatchEvent(new CustomEvent(`close-${id}`));
    };
}