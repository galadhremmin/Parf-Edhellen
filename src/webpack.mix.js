const { mix } = require('laravel-mix');
const path = require('path');
const config = require('dotenv').config();

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
            'elfdict': path.resolve(__dirname, 'resources/assets/js/_ed/'),

            'ed-components/comments': 'elfdict/components/comments.jsx',
            'ed-components/dialog': 'elfdict/components/dialog.jsx',
            'ed-components/error-list': 'elfdict/components/error-list.jsx',
            'ed-components/markdown-editor': 'elfdict/components/markdown-editor.jsx',
            'ed-components/language-select': 'elfdict/components/language-select.jsx',
            'ed-components/gloss-select': 'elfdict/components/gloss-select.jsx',

            'ed-api': 'elfdict/api.js',
            'ed-config': 'elfdict/config.js',
            'ed-form': 'elfdict/form.js',
            'ed-promise': 'elfdict/promise.js',
            'ed-session-storage-state': 'elfdict/session-storage-state.js',
            'ed-scrolling': 'elfdict/scrolling.js',
            'ed-tengwar': 'elfdict/tengwar.js'
        }
    }
});

const outputDir = `public/v${process.env.ED_VERSION}`;

mix.extract([
    'core-js/es6/map', 'core-js/es6/set',
    'react', 'react-dom', 'react-router-dom',
    'react-redux', 'redux', 'redux-thunk',
    'react-autosuggest', 
    'html-to-react',
    'axios', 'retry-axios',
    'classnames',
    /* These components are excuded from _vendor.js_ as they are limited to restricted visitors 
    'ed-components/dialog',
    'ed-components/error-list',
    'ed-components/gloss-select',
    */
    'ed-components/markdown-editor',
    'ed-components/language-select',
    'ed-components/comments',
    'ed-config',
    'ed-form',
    'ed-promise',
    'ed-session-storage-state',
    'ed-tengwar'
], `${outputDir}/js/vendor.js`);

mix.react([
    'resources/assets/js/date.js',
    'resources/assets/js/transcription.js',
    'resources/assets/js/navigation.js',
    'resources/assets/js/_shared/error.js',
    'resources/assets/js/search/index.jsx'
], `${outputDir}/js/global.js`);

mix.react([
    'resources/assets/js/_plugins-restricted/index.jsx'
], `${outputDir}/js/global-plugins-restricted.js`);

mix.react([
    'resources/assets/js/_plugins-admin/index.jsx'
], `${outputDir}/js/global-plugins-admin.js`);

mix.combine([
    'node_modules/glaemscribe/js/glaemscribe.js',
    'node_modules/glaemscribe/js/charsets/tengwar_ds_annatar.cst.js',
    'node_modules/glaemscribe/js/modes/adunaic.glaem.js',
    'node_modules/glaemscribe/js/modes/blackspeech.glaem.js',
    'node_modules/glaemscribe/js/modes/quenya.glaem.js',
    'node_modules/glaemscribe/js/modes/sindarin-beleriand.glaem.js',
    'node_modules/glaemscribe/js/modes/sindarin.glaem.js',
    'node_modules/glaemscribe/js/modes/telerin.glaem.js',
    'node_modules/glaemscribe/js/modes/westron.glaem.js',
], `${outputDir}/js/glaemscribe.js`);

mix.js([
    'node_modules/babel-polyfill/dist/polyfill.js',
    'resources/assets/js/_ie/polyfills.js',
], `${outputDir}/js/ie.js`);

mix.js([
    'resources/assets/js/markdown/index.jsx'
], `${outputDir}/js/markdown.js`);

mix.js([
    'resources/assets/js/sentence/index.jsx'
], `${outputDir}/js/sentence.js`);

mix.js([
    'resources/assets/js/sentence/admin.jsx'
], `${outputDir}/js/sentence-admin.js`);

mix.js([
    'resources/assets/js/comment/index.jsx'
], `${outputDir}/js/comment.js`);

mix.js([
    'resources/assets/js/gloss/admin.jsx'
], `${outputDir}/js/gloss-admin.js`);

mix.js([
    'resources/assets/js/flashcard/index.jsx'
], `${outputDir}/js/flashcard.js`);

mix.js([
    'resources/assets/js/system-errors/index.jsx'
], `${outputDir}/js/system-errors-admin.js`);

// .sourceMaps();

mix.sass('resources/assets/sass/app.scss', `${outputDir}/css`);
mix.sass('resources/assets/sass/app.flashcard.scss', `${outputDir}/css`);
mix.sass('resources/assets/sass/app.sentence.scss', `${outputDir}/css`);
mix.sass('resources/assets/sass/app.sentences.scss', `${outputDir}/css`);
mix.sass('resources/assets/sass/app.timeline.scss', `${outputDir}/css`);
mix.sass('resources/assets/sass/app.discuss.scss', `${outputDir}/css`);
