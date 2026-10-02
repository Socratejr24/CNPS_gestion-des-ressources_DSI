import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Base
                'resources/css/app.css',
                'resources/js/app.js',

                // Layouts
                'resources/css/layouts/app.css',

                // Auth
                'resources/css/auth/login.css',

                
                // Dashboard
                'resources/css/dashboard/index.css',
                'resources/js/dashboard/charts.js',

                // Composants
                'resources/css/components/buttons.css',
                'resources/css/components/forms.css',
                'resources/css/components/badges.css',
                'resources/css/components/icons.css',
                'resources/css/components/cards.css',
                'resources/css/components/tables.css',
                'resources/css/components/alerts.css',
                'resources/css/components/modals.css',
                'resources/css/components/toasts.css',
                'resources/css/components/filters.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});