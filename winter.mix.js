let mix = require('laravel-mix');
mix.setPublicPath(__dirname);

mix.postCss('assets/css/src/app.css', 'assets/css/dist/backend.css', [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    require('tailwindcss'),
    require('autoprefixer'),
]);

mix.js('assets/js/src/app.js', 'assets/js/dist/app.js').vue({ version: 3 });
