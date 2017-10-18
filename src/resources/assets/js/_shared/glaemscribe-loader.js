/**
 * Retrieve the current script path, which is necessary in order to know where Glaemscribe is located.
 * @returns String
 */
const currentScriptPath = () => {

    var scripts = document.querySelectorAll( 'script[src]' );
    var currentScript = scripts[ scripts.length - 1 ].src;
    var currentScriptChunks = currentScript.split( '/' );
    var currentScriptFile = currentScriptChunks[ currentScriptChunks.length - 1 ];

    return currentScript.replace( currentScriptFile, '' );
}

/**
 * Loads Glaemscribe by adding a <script> file to document head.
 */
const loadGlaemscribe = () => {
    // Load the Glaemscribe component, as it is a prerequisite for the plugin components.
    if (! window.Glaemscribe) {
        const glaemscribeScript = document.createElement('script');
        glaemscribeScript.src = `${currentScriptPath()}glaemscribe.js`;
        glaemscribeScript.async = true;
        glaemscribeScript.type = 'text/javascript';

        document.head.appendChild(glaemscribeScript);
    }
};

export default loadGlaemscribe;
