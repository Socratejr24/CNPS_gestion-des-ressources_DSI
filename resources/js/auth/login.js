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
                    // ⚠️ BACKEND À BRANCHER PLUS TARD
                    // Pour l'instant, on simule un délai puis on teste
                    // les identifiants en dur.
                    await new Promise(r => setTimeout(r, 600));

                    const result = this.attemptLogin(payload);

                    if (!result.success) {
                        this.errorMessage = result.message;
                    } else {
                        console.log('✅ Connexion réussie :', result.user);
                        // TODO: rediriger vers le dashboard
                    }
                } catch (e) {
                    this.errorMessage = "Une erreur est survenue. Veuillez réessayer.";
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            },

            /**
             *  Comptes de test en dur — à remplacer par un appel API
             * quand on aura branché le backend.
             */
            attemptLogin({ mode, identifiant, password }) {
                const comptesTest = {
                    agent: [
                        { identifiant: '123456', password: 'agent123', nom: 'Kouassi A.' },
                        { identifiant: '654321', password: 'agent123', nom: 'Diallo M.' },
                    ],
                    prestataire: [
                        { identifiant: 'presta@cnps.ci', password: 'presta123', nom: 'Prestataire Test' },
                    ],
                };

                const found = comptesTest[mode]?.find(
                    c => c.identifiant === identifiant && c.password === password
                );

                if (!found) {
                    return {
                        success: false,
                        message: mode === 'agent'
                            ? 'Matricule ou mot de passe incorrect.'
                            : 'Adresse e-mail ou mot de passe incorrect.',
                    };
                }

                return { success: true, user: found };
            },
        };
    };
}