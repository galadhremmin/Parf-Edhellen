const EDConfig = {
    apiPathName: '/api/v1', // path to API w/o trailing slash!
    apiValidationErrorStatusCode: 422,
    
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
    api: path => EDConfig.apiPathName + (path[0] !== '/' ? '/' : '') + path,

    /**
     * Convenience method for generating window messages
     */
    message: (source, payload) => window.postMessage({ source, payload }, EDConfig.messageDomain)
};

const languages = JSON.parse(document.getElementById('ed-preloaded-languages').textContent);

export default EDConfig;
