const EDConfig = {
    apiPathName: '/api/v1', // path to API w/o trailing slash!
    apiValidationErrorStatusCode: 422,
    
    messageDomain: window.location.origin,
    messageNavigateName: 'ednavigate',

    /**
     * Gets all languages
     */
    languages: () => languages,

    userId: () => parseInt(document.body.dataset['userId'], 10),

    findLanguage: (v, key = 'id', cmpFunc = (a, b) => a === b) => {
        const categories = Object.keys(languages);

        for (let i = categories.length - 1; i >= 0; i -= 1) {
            const subLanguages = languages[categories[i]];

            for (let j = subLanguages.length - 1; j >= 0; j -= 1) {
                if (cmpFunc(subLanguages[j][key], v)) {
                    return subLanguages[j];
                }
            }
        }

        return undefined;
    },

    /**
     * Convenience method for generating API paths
     * @param path
     */
    api: path => EDConfig.apiPathName + (path[0] !== '/' ? '/' : '') + path,

    /**
     * Convenience method for generating window messages
     */
    message: (source, payload) => window.postMessage({ source, payload }, EDConfig.messageDomain),

    pluginsFor: (context) => plugins.hasOwnProperty(context) ? plugins[context] : [],

    /**
     * Associates the specified array of plugins with the context name given.
     * @param context - name of plugin context
     * @param arrayOfPluginComponents - array of React components
     */
    addPlugins: (context, arrayOfPluginComponents) => plugins[context] = [...(EDConfig.pluginsFor(context)), ...arrayOfPluginComponents]
};

const languages = JSON.parse(document.getElementById('ed-preloaded-languages').textContent);
const plugins = {};

export default EDConfig;
