import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Base (inclut Tailwind, polices et styles des composants Blade)
                'resources/css/app.css',
                'resources/js/app.js',

                // Layouts
                'resources/css/layouts/app.css',
                'resources/css/layouts/sidebar.css',

                // Pages
                'resources/css/auth/login.css',
                'resources/css/dashboard/index.css',
                'resources/js/dashboard/charts.js',
                'resources/css/demandes/index.css',
                'resources/css/admin/index.css',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
