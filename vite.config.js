import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                
                'resources/css/app.css',
                'resources/css/auth/login.css',
                'resources/css/layouts/app.css',
                'resources/js/app.js',
                'resources/css/components/buttons.css',//Nouveau
                'resources/css/components/forms.css',//Nouveau
                'resources/css/components/badges.css',//Nouveau
                'resources/css/components/icons.css'//Nouveau
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});