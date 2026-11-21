export function registerVerifyOtpForm() {
    window.verifyOtpForm = function () {
        // Récupérer l'email depuis l'URL (?email=xxx)
        const urlParams = new URLSearchParams(window.location.search);
        const emailFromUrl = urlParams.get('email') || '';

        return {
            email: emailFromUrl,
            otp: '',
            loading: false,
            errorMessage: '',

            async submit() {
                this.errorMessage = '';

                // Validation client
                if (!/^\d{6}$/.test(this.otp)) {
                    this.errorMessage = 'Le code doit contenir exactement 6 chiffres.';
                    return;
                }

                this.loading = true;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const response = await fetch('/test-api/auth/verify-otp', {
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
                        }),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // Erreur (code invalide ou expiré)
                        if (data.errors) {
                            const firstError = Object.values(data.errors)[0];
                            this.errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                        } else {
                            this.errorMessage = data.message || 'Code invalide ou expiré.';
                        }
                        this.loading = false;
                        return;
                    }

                    // ✅ Code valide → redirection vers reset password
                    window.location.href = '/reset-password?email=' 
                        + encodeURIComponent(this.email) 
                        + '&otp=' 
                        + encodeURIComponent(this.otp);

                } catch (e) {
                    console.error(e);
                    this.errorMessage = 'Erreur réseau. Vérifiez votre connexion.';
                    this.loading = false;
                }
            },

            async resendCode() {
                this.errorMessage = '';
                this.loading = true;

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    const response = await fetch('/test-api/auth/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ email: this.email }),
                    });

                    const data = await response.json();

                    if (response.ok) {
                        if (window.showToast) {
                            window.showToast('success', 'Code renvoyé', 'Un nouveau code a été envoyé à votre adresse email.');
                        }
                    } else {
                        this.errorMessage = data.message || 'Impossible de renvoyer le code.';
                    }
                } catch (e) {
                    console.error(e);
                    this.errorMessage = 'Erreur réseau.';
                } finally {
                    this.loading = false;
                }
            },
        };
    };
}