import Alpine from 'alpinejs';
import { registerLoginForm } from './auth/login';
import { registerAppLayout } from './layouts/app';
import { registerToasts } from './components/toast';
import { registerModals } from './components/modal';

// Enregistrement des composants Alpine + helpers
registerLoginForm();
registerAppLayout();
registerToasts();
registerModals();

window.Alpine = Alpine;
Alpine.start();