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
mix.react([
  //'node_modules/axios/dist/axios.js',
  'resources/assets/js/search/index.jsx'
], 'public/js/global.js');
mix.sass('resources/assets/sass/app.scss', 'public/css');
