import EDConfig from 'ed-config';

import EDEditGlossPlugin from './book-gloss-toolbar/edit';
import EDDeleteGlossPlugin from './book-gloss-toolbar/delete';

window.addEventListener('load', () => {
    EDConfig.addPlugins('book-gloss-toolbar', [ EDEditGlossPlugin, EDDeleteGlossPlugin ]);
});
