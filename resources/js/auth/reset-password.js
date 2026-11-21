export function registerResetPasswordForm() {
    window.resetPasswordForm = function () {
        // Récupérer email et otp depuis l'URL (?email=xxx&otp=xxx)
        const urlParams = new URLSearchParams(window.location.search);
        const email = urlParams.get('email') || '';
        const otp = urlParams.get('otp') || '';

        return {
            email: email,
            otp: otp,
            password: '',
            passwordConfirmation: '',
            showPassword: false,
            showConfirmation: false,
            loading: false,
            errorMessage: '',
            successMessage: '',

            async submit() {
                this.errorMessage = '';
                this.successMessage = '';

                // Validation client
                if (this.password.length < 8) {
                    this.errorMessage = 'Le mot de passe doit contenir au moins 8 caractères.';
                    return;
                }

                if (this.password !== this.passwordConfirmation) {
                    this.errorMessage = 'Les mots de passe ne correspondent pas.';
                    return;
                }

                // Vérifier que l'email et l'otp sont présents
                if (!this.email || !this.otp) {
                    this.errorMessage = 'Session expirée. Recommencez la procédure.';
                    return;
                }

                this.loading = true;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const response = await fetch('/test-api/auth/reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            email: this.email,
                            otp: this.otp,
                            password: this.password,
                            password_confirmation: this.passwordConfirmation,
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Erreur (OTP expiré, mot de passe invalide, etc.)
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            this.errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                        } else {
                            this.errorMessage = data.message || 'Une erreur est survenue.';
                        }
                        this.loading = false;
                        return;
                    }

                    // ✅ Succès
                    this.successMessage = 'Mot de passe réinitialisé avec succès ! Redirection...';

                    // Toast de succès
                    if (window.showToast) {
                        window.showToast('success', 'Mot de passe réinitialisé', 'Vous pouvez maintenant vous connecter.');
                    }

                    // Redirection vers /login après 2s
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2000);

                } catch (e) {
                    console.error(e);
                    this.errorMessage = 'Erreur réseau. Vérifiez votre connexion.';
                    this.loading = false;
                }
            },
        };
    };
}