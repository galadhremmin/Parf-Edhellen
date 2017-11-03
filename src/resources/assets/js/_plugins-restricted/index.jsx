import EDConfig from 'ed-config';
import loadGlaemscribe from '../_shared/glaemscribe-loader';
import EDEditGlossPlugin from './book-gloss-toolbar/edit';

const load = () => {
    loadGlaemscribe();
    EDConfig.addPlugins('book-gloss-toolbar', [ EDEditGlossPlugin ]);
}

window.addEventListener('load', function () {
    window.setTimeout(load, 0);
});
