const loadGlaemscribe = () => {
    // Load the Glaemscribe component, as it is a prerequisite for the plugin components.
    if (! window.Glaemscribe) {
        const glaemscribeScript = document.createElement('script');
        glaemscribeScript.src = '/js/glaemscribe.js';
        glaemscribeScript.async = true;
        glaemscribeScript.type = 'text/javascript';

        document.head.appendChild(glaemscribeScript);
    }
};

export default loadGlaemscribe;
