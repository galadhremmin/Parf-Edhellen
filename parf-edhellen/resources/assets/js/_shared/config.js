(function () {

    if (window.EDConfig !== undefined) {
        throw 'EDConfig is already defined';
    }

    const config = {
        apiPathName: '/api/v1', // path to API w/o trailing slash!

        /**
         * Convenience method for generating API paths
         * @param path
         */
        api: path => config.apiPathName + (path[0] !== '/' ? '/' : '') + path
    };

    window.EDConfig = config;
})();