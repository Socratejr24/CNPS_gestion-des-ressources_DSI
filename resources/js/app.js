import Alpine from 'alpinejs';
import { registerLoginForm } from './auth/login';
import { registerAppLayout } from './layouts/app';
import { registerToasts } from './components/toast';
import { registerModals } from './components/modal';
import { registerDashboard } from './dashboard/index';
import { registerResetPasswordForm } from './auth/reset-password';
import { registerForgotPasswordForm } from './auth/forgot-password';
import { registerVerifyOtpForm } from './auth/verify-otp';
import '@fortawesome/fontawesome-free/css/all.min.css';

// ✅ Enregistrement des composants Alpine
registerLoginForm();
registerAppLayout();
registerToasts();
registerModals();
registerForgotPasswordForm();
registerDashboard();
registerVerifyOtpForm();
registerResetPasswordForm();




// ✅ Démarrage d'Alpine APRÈS le chargement du DOM
window.Alpine = Alpine;

document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
    console.log('✅ Alpine démarré');
});