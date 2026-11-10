import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        proxy: {
            '/api': 'http://127.0.0.1:8088',
            '/sanctum': 'http://127.0.0.1:8088',
            '/storage': 'http://127.0.0.1:8088',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
