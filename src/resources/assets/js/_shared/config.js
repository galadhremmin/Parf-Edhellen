(function () {
    const config = {
        apiPathName: '/api/v1', // path to API w/o trailing slash!
        messageDomain: window.location.origin,
        messageNavigateName: 'ednavigate',

        /**
         * Gets all languages
         */
        languages: () => languages, 

        /**
         * Convenience method for generating API paths
         * @param path
         */
        api: path => config.apiPathName + (path[0] !== '/' ? '/' : '') + path,

        /**
         * Convenience method for generating window messages
         */
        message: (source, payload) => window.postMessage({ source, payload }, config.messageDomain)
    };

    const languages = JSON.parse(document.getElementById('ed-preloaded-languages').textContent);

    if (window.EDConfig !== undefined) {
        throw 'EDConfig is already defined';
    }
    window.EDConfig = config;
})();