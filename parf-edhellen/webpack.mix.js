const { mix } = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.extract([
    'react', 'react-dom',
    'react-redux', 'redux',
    'html-to-react',
    'axios',
    'classnames'
]);

mix.react([
    'resources/assets/js/search/index.jsx'
], 'public/js/global.js');

mix.js([
    'resources/assets/js/markdown/index.jsx'
], 'public/js/markdown.js');

mix.sass('resources/assets/sass/app.scss', 'public/css');
