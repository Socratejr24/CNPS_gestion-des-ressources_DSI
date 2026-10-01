import Alpine from 'alpinejs';
import { registerLoginForm } from './auth/login';
import { registerAppLayout } from './layouts/app';

// Enregistrement des composants Alpine
registerLoginForm();
registerAppLayout();

window.Alpine = Alpine;
Alpine.start();