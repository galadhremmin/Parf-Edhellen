const { mix } = require('laravel-mix');
const path = require('path');

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
mix.webpackConfig({
    resolve: {
        alias: {
            'ed-shared': path.resolve(__dirname, 'resources/assets/js/_shared/'),

            'ed-components/error-list': 'ed-shared/components/error-list.jsx',
            'ed-components/markdown-editor': 'ed-shared/components/markdown-editor.jsx',

            'ed-config': 'ed-shared/config.js',
            'ed-form': 'ed-shared/form.js',
            'ed-promise': 'ed-shared/promise.js',
            'ed-session-storage-state': 'ed-shared/session-storage-state.js',
        }
    }
});

mix.extract([
    'react', 'react-dom', 'react-router-dom',
    'react-redux', 'redux',
    'html-to-react',
    'axios',
    'classnames',
    'smoothscroll-polyfill',
    'ed-components/error-list',
    'ed-components/markdown-editor',
    'ed-config',
    'ed-form',
    'ed-promise',
    'ed-session-storage-state'
]);

mix.react([
    'resources/assets/js/_shared/config.js',
    'resources/assets/js/navigation.js',
    'resources/assets/js/search/index.jsx'
], 'public/js/global.js');

mix.js([
    'resources/assets/js/markdown/index.jsx'
], 'public/js/markdown.js');

mix.js([
    'resources/assets/js/sentence/index.jsx'
], 'public/js/sentence.js');

mix.js([
    'resources/assets/js/sentence/admin.jsx'
], 'public/js/sentence-admin.js');

mix.sass('resources/assets/sass/app.scss', 'public/css');
