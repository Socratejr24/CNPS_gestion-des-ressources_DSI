/**
 * Login Alpine.js — connecté à l'API réelle.
 */
export function registerLoginForm() {

    window.loginForm = function () {
        return {
            // --- État ---
            mode: 'agent',
            showPassword: false,
            loading: false,
            errorMessage: '',

            // --- Computed ---
            get isPrestataire() {
                return this.mode === 'prestataire';
            },

            get identifiantLabel() {
                return this.isPrestataire ? 'Identifiant' : 'Matricule';
            },

            get identifiantPlaceholder() {
                return this.isPrestataire
                    ? 'Ex : jkouassi1234'
                    : 'Ex : MXXXX';
            },

            get identifiantType() {
                return 'text';
            },

            get hint() {
                return this.isPrestataire
                    ? "Utilisez les identifiants reçus par e-mail de l'administrateur."
                    : "Utilisez vos identifiants de l'annuaire CNPS.";
            },

            // --- Actions ---
            switchMode(newMode) {
                this.mode = newMode;
                this.errorMessage = '';
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
                    // Récupérer le CSRF token depuis la meta
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const response = await fetch('/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',  //  envoie les cookies de session
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.errorMessage = data.message || 'Identifiants invalides.';
                        this.loading = false;
                        return;
                    }

                    //  Succès → redirection
                    console.log('Connexion réussie :', data.user);
                    window.location.href = data.redirect || '/dashboard';

                } catch (e) {
                    this.errorMessage = "Erreur réseau. Vérifiez votre connexion.";
                    console.error(e);
                    this.loading = false;
                }
            },
        };
    };
}