import Alpine from 'alpinejs';
import { registerLoginForm } from './auth/login';
import { registerAppLayout } from './layouts/app';
import { registerToasts } from './components/toast';
import { registerModals } from './components/modal';
import { registerDashboard } from './dashboard/index';
import '@fortawesome/fontawesome-free/css/all.min.css';

// Enregistrement des composants Alpine + helpers
registerLoginForm();
registerAppLayout();
registerToasts();
registerModals();
registerDashboard();

window.Alpine = Alpine;
Alpine.start();