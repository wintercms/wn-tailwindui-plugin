let mix = require('laravel-mix');
mix.setPublicPath(__dirname);

mix.webpackConfig({
    resolve: {
        alias: {
            vue$: 'vue/dist/vue.esm-bundler.js',
        }
    }
});

mix.postCss('assets/src/css/app.css', 'assets/dist/css/backend.css', [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    require('tailwindcss'),
    require('autoprefixer'),
]);

mix.postCss('assets/src/css/winter.css', 'assets/dist/css/winter.css', [
    require('postcss-nesting'),
    require('postcss-nested-import'),
]);

mix.js('assets/src/js/app.js', 'assets/dist/js/app.js').vue({ version: 3 });
