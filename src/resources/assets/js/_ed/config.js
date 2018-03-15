const EDConfig = {
    messageDomain: window.location.origin,
    messageNavigateName: 'ednavigate',

    /**
     * Gets whether the current session has administrative permissions.
     */
    admin: () => document.body.classList.contains('ed-admin'),
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

const plugins = {};

export default EDConfig;
