import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    build: {
        outDir: 'assets/dist',
    },
    resolve: {
        alias:{
            vue: 'vue/dist/vue.esm-bundler.js'
        }
    },
    plugins: [
        laravel({
            publicDirectory: 'assets/dist',
            input: [
                'assets/src/css/tailwind.css',
                'assets/src/css/winter.css',
                'assets/src/js/app.js',
            ],
            refresh: {
                paths: [
                    './**/*.htm',
                    './**/*.block',
                    'assets/**/*.css',
                    'assets/**/*.js',
                    'assets/**/*.vue',
                ]
            },
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // The Vue plugin will re-write asset URLs, when referenced
                    // in Single File Components, to point to the Laravel web
                    // server. Setting this to `null` allows the Laravel plugin
                    // to instead re-write asset URLs to point to the Vite
                    // server instead.
                    base: null,

                    // The Vue plugin will parse absolute URLs and treat them
                    // as absolute paths to files on disk. Setting this to
                    // `false` will leave absolute URLs un-touched so they can
                    // reference assets in the public directory as expected.
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
