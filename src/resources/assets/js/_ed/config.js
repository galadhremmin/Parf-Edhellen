const EDConfig = {
    messageDomain: window.location.origin,
    messageNavigateName: 'ednavigate',

    /**
     * Gets whether the current session has administrative permissions.
     */
    admin: () => document.body.classList.contains('ed-admin'),
    userId: () => parseInt(document.body.dataset['userId'], 10),

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
