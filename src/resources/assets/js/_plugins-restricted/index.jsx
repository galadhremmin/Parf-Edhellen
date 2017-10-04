import EDConfig from 'ed-config';
import loadGlaemscribe from '../_shared/glaemscribe-loader';
import EDEditGlossPlugin from './book-gloss-toolbar/edit';

window.addEventListener('load', function () {
    loadGlaemscribe();
    EDConfig.addPlugins('book-gloss-toolbar', [ EDEditGlossPlugin ]);
});
