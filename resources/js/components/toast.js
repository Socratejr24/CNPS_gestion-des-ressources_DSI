/**
 * Système de toasts Alpine.js
 *
 * Utilisation :
 *   window.showToast('success', 'Enregistré', 'Message optionnel');
 *   window.showToast('error', 'Erreur', 'Impossible de contacter le serveur.');
 *   window.showToast('warning', 'Attention', 'Vérifiez les informations.');
 *   window.showToast('info', 'Information', 'Nouvelle version disponible.');
 *
 * Depuis Blade / Alpine :
 *   <button @click="showToast('success', 'OK', 'C\'est fait !')">Test</button>
 */
export function registerToasts() {

    /**
     * Composant Alpine qui gère la pile de toasts.
     */
    window.toastManager = function () {
        return {
            toasts: [],
            counter: 0,

                      init() {
                // ⚠️ Protection : ne jamais ajouter le listener 2 fois
                if (window.__toastListenerBound) {
                    return;
                }
                window.__toastListenerBound = true;

                // Écouter l'événement window 'toast'
                window.addEventListener('toast', (e) => {
                    this.add(e.detail);
                });

                // Toasts flash Laravel (injectés côté Blade)
                const flashToasts = document.querySelectorAll('[data-flash-toast]');
                flashToasts.forEach(el => {
                    try {
                        const data = JSON.parse(el.dataset.flashToast);
                        this.add(data);
                        el.remove();
                    } catch (err) {
                        console.error('Toast flash invalide :', err);
                    }
                });
            },
            /**
             * Ajoute un toast à la pile.
             * @param {Object} opts
             * @param {'success'|'error'|'warning'|'info'} opts.type
             * @param {string} opts.title
             * @param {string} [opts.text]
             * @param {number} [opts.duration] - en ms (défaut 4000)
             */
            add({ type = 'info', title = '', text = '', duration = 4000 }) {
                const id = ++this.counter;
                this.toasts.push({ id, type, title, text });

                setTimeout(() => this.remove(id), duration);
            },

            /**
             * Retire un toast avec une petite animation de sortie.
             */
            remove(id) {
                const toast = this.toasts.find(t => t.id === id);
                if (!toast) return;

                toast.leaving = true;

                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 250);
            },

            /**
             * Retourne le SVG correspondant au type de toast.
             */
            iconFor(type) {
                const icons = {
                    success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
                    error:   '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
                    warning: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
                    info:    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
                };
                return icons[type] || icons.info;
            },
        };
    };

    /**
     * Helper global :
     *   showToast('success', 'Enregistré', 'Message optionnel', 4000)
     */
    window.showToast = function (type, title, text = '', duration = 4000) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { type, title, text, duration }
        }));
    };
}