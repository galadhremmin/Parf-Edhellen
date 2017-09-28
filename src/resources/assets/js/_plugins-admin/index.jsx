import EDConfig from 'ed-config';

import EDEditGlossPlugin from './book-gloss-toolbar/edit';
import EDDeleteGlossPlugin from './book-gloss-toolbar/delete';

window.addEventListener('load', function () {
    // Load the Glaemscribe component, as it is a prerequisite for the plugin components.
    if (! window.Glaemscribe) {
        const glaemscribeScript = document.createElement('script');
        glaemscribeScript.src = '/js/glaemscribe.js';
        glaemscribeScript.async = true;
        glaemscribeScript.type = 'text/javascript';

        document.head.appendChild(glaemscribeScript);
    }

    EDConfig.addPlugins('book-gloss-toolbar', [ EDEditGlossPlugin, EDDeleteGlossPlugin ]);
});
