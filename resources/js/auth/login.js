/**
 * Logique Alpine.js de la page de connexion.
 * Exposé comme fonction globale `loginForm` pour être utilisé
 * dans le Blade via x-data="loginForm()".
 */
export function registerLoginForm() {

    window.loginForm = function () {
        return {
            // --- État ---
            mode: 'agent',              // 'agent' | 'prestataire'
            showPassword: false,
            loading: false,
            errorMessage: '',

            // --- Computed helpers ---
            get isPrestataire() {
                return this.mode === 'prestataire';
            },

            get identifiantLabel() {
                return this.isPrestataire ? 'Identifiants' : 'Matricule';
            },

            get identifiantPlaceholder() {
                return this.isPrestataire
                    ? 'prenom.nom@entreprise.ci'
                    : 'Ex : m-XXXX';
            },

            get identifiantType() {
                return this.isPrestataire ? 'email' : 'text';
            },

            get hint() {
                return this.isPrestataire
                    ? "Utilisez les identifiants reçus par e-mail de l'administrateur."
                    : "Utilisez vos identifiants de l'annuaire CNPS.";
            },

            // --- Actions ---
            switchMode(newMode) {
                this.mode = newMode;
                this.errorMessage = '';   // reset erreur au changement
            },

            togglePassword() {
                this.showPassword = !this.showPassword;
            },

            async submit(event) {
                this.errorMessage = '';
                this.loading = true;

                const formData = new FormData(event.target);
                const payload = {
                    mode: this.mode,
                    identifiant: formData.get('identifiant'),
                    password: formData.get('password'),
                };

                try {
                    const response = await fetch(event.target.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }

                    if (response.status === 419) {
                        // Jeton CSRF expiré (page restée ouverte trop longtemps)
                        this.errorMessage = 'Votre session a expiré. La page va être rechargée.';
                        setTimeout(() => window.location.reload(), 1500);
                        return;
                    }

                    // 422 : validation / identifiants incorrects — 429 : trop de tentatives
                    this.errorMessage = data.message
                        ?? Object.values(data.errors ?? {})[0]?.[0]
                        ?? 'Une erreur est survenue. Veuillez réessayer.';
                } catch (e) {
                    this.errorMessage = 'Impossible de joindre le serveur. Vérifiez votre connexion.';
                    console.error(e);
                }

                this.loading = false;
            },
        };
    };
}