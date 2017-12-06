import EDConfig from 'ed-config';
import loadGlaemscribe from '../_shared/glaemscribe-loader';
import EDEditGlossPlugin from './book-gloss-toolbar/edit';
import EDDeleteGlossPlugin from './book-gloss-toolbar/delete';

const load = () => {
    loadGlaemscribe();
    EDConfig.addPlugins('book-gloss-toolbar', [ EDEditGlossPlugin, EDDeleteGlossPlugin ]);
};

load();
