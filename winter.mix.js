let mix = require('laravel-mix');
mix.setPublicPath(__dirname);

const postcssColorMod = require('postcss-color-mod-function');

mix.postCss('assets/css/src/app.css', 'assets/css/dist/backend.css', [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    require('tailwindcss'),
    require('autoprefixer'),
    postcssColorMod({
        unresolved: 'ignore',
    }),
]);

mix.js('assets/js/src/app.js', 'assets/js/dist/app.js').vue({ version: 3 });
