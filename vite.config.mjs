import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'assets/dist',
        terserOptions: {
            format: {
              comments: false
            }
        }
    },
    plugins: [
        laravel({
            publicDirectory: 'assets/dist',
            input: [
                'assets/src/css/app.css',
                'assets/src/js/app.js',
            ],
            refresh: {
                paths: [
                    './**/*.htm',
                    './**/*.block',
                    'assets/src/**/*.css',
                    'assets/src/**/*.js',
                ]
            },
        }),
    ],
});
