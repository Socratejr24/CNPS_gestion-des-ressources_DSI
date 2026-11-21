/**
 * Logique Alpine.js de la page de connexion.
 * Exposé comme fonction globale `loginForm` pour être utilisé
 * dans le Blade via x-data="loginForm()".
 */
const CLE_MODE = 'cnps.login.mode';

function lireMode() {
    try {
        const mode = window.localStorage.getItem(CLE_MODE);
        return mode === 'prestataire' ? 'prestataire' : 'agent';
    } catch {
        return 'agent';
    }
}

function memoriserMode(mode) {
    try {
        window.localStorage.setItem(CLE_MODE, mode);
    } catch {
        // stockage indisponible (navigation privée…) : sans conséquence
    }
}

export function registerLoginForm() {

    window.loginForm = function () {
        return {
            // --- État ---
            mode: 'agent',              // 'agent' | 'prestataire'
            showPassword: false,
            capsLock: false,
            loading: false,
            errorMessage: '',

            init(erreurServeur = '') {
                this.mode = lireMode();
                this.errorMessage = erreurServeur;
            },

            // --- Libellés selon le type de compte ---
            // Les agents se connectent avec leur matricule (LDAP),
            // les prestataires avec leur adresse e-mail (compte local).
            get identifiantLabel() {
                return this.mode === 'prestataire' ? 'Adresse e-mail' : 'Matricule';
            },

            get identifiantPlaceholder() {
                return this.mode === 'prestataire' ? 'prenom.nom@entreprise.ci' : 'Votre matricule';
            },

            get identifiantType() {
                return this.mode === 'prestataire' ? 'email' : 'text';
            },

            // --- Actions ---
            switchMode(mode) {
                if (this.mode === mode) return;
                this.mode = mode;
                this.errorMessage = '';
                memoriserMode(mode);
                this.$nextTick(() => this.$refs.identifiant?.focus());
            },

            async submit(event) {
                this.errorMessage = '';
                this.loading = true;

                const formData = new FormData(event.target);
                const payload = {
                    mode: this.mode,
                    identifiant: (formData.get('identifiant') ?? '').trim(),
                    password: formData.get('password'),
                };

                if (!payload.identifiant || !payload.password) {
                    this.errorMessage = `Saisissez votre ${this.identifiantLabel.toLowerCase()} et votre mot de passe.`;
                    this.loading = false;
                    return;
                }

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
                        memoriserMode(this.mode);
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
