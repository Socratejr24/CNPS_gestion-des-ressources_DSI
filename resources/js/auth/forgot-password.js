export function registerForgotPasswordForm() {
    window.forgotPasswordForm = function () {
        return {
            email: '',
            loading: false,
            errorMessage: '',
            successMessage: '',

            async submit() {
                this.errorMessage = '';
                this.successMessage = '';
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

                    if (!response.ok) {
                        // Gérer les erreurs
                        if (data.errors?.email) {
                            this.errorMessage = data.errors.email[0];
                        } else {
                            this.errorMessage = data.message || 'Une erreur est survenue.';
                        }
                        this.loading = false;
                        return;
                    }

                    // ✅ Succès
                    this.successMessage = 'Un code vous a été envoyé par email.';
                    
                    // Redirection vers la page OTP après 1.5s
                    setTimeout(() => {
                        window.location.href = '/verify-otp?email=' + encodeURIComponent(this.email);
                    }, 1500);

                } catch (e) {
                    console.error(e);
                    this.errorMessage = 'Erreur réseau. Vérifiez votre connexion.';
                    this.loading = false;
                }
            },
        };
    };
}