import axios from 'axios';

const EDAPI = {
    apiPathName: '/api/v2', // path to API w/o trailing slash!
    apiErrorMethod: 'utility/error',
    apiValidationErrorStatusCode: 422,

    /**
     * Execute a DELETE request.
     */
    delete: function (apiMethod) { 
        return this._consume(axios.delete, apiMethod);
    },

    /**
     * Execute a HEAD request.
     */
    head: function (apiMethod) {
        return this._consume(axios.head, apiMethod);
    },

    /**
     * Execute a GET request.
     */
    get: function (apiMethod) {
        return this._consume(axios.get, apiMethod);
    },

    /**
     * Execute a POST request.
     */
    post: function (apiMethod, payload) {
        return this._consume(axios.post, apiMethod, payload || {});
    },

    /**
     * Execute a PUT request.
     */
    put: function (apiMethod, payload) {
        return this._consume(axios.put, apiMethod, payload || {});
    },

    /**
     * Register the specified error.
     */
    error: function (message, url, error) {
        return this.post(this.apiErrorMethod, { message, url, error });
    },

    /**
     * Gets all languages.
     */
    languages: function (id = undefined, key = 'id', cmpFunc = (a, b) => a === b) {
        let promise = null;
        
        let languages = this._cachedLanguages;
        if (! languages) {
            const json = window.sessionStorage.getItem('ed.languages');
            if (json) {
                languages = JSON.parse(json);
            }

            if (languages) {
                this._cachedLanguages = languages;
            }
        }

        if (languages) {
            promise = Promise.resolve(languages);
        } else {
            promise = this.get('book/languages')
                .then(resp => {
                    window.sessionStorage.setItem('ed.languages', JSON.stringify(resp.data));
                    return resp.data;
                });
        }

        return promise.then(this._filterLanguages.bind(this, id, key, cmpFunc));
    },

    _filterLanguages: function (id, key, cmpFunc, languages) {
        if (id === undefined) {
            return languages;
        }

        const categories = Object.keys(languages);

        for (let i = categories.length - 1; i >= 0; i -= 1) {
            const subLanguages = languages[categories[i]];

            for (let j = subLanguages.length - 1; j >= 0; j -= 1) {
                if (cmpFunc(subLanguages[j][key], id)) {
                    return subLanguages[j];
                }
            }
        }

        return undefined;
    },

    /**
     * Combines an absolute path based on the API method path.
     */
    _absPath: function (path) {
        return path[0] === '/' ? path : this.apiPathName + '/' + path;
    },

    /**
     * Default XMLHTTPRequest configuration.
     */
    _config: () => ({
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }),

    /**
     * Executes the specified HTTP method and manages errors gracefully.
     */
    _consume: function (factory, apiMethod, payload) {
        const config = this._config();
        const hasBody = payload !== undefined;
        return factory
            .call(axios, this._absPath(apiMethod), 
                hasBody ? payload : config,   
                hasBody ? config : undefined 
            )
            .catch(this._handleError.bind(this, apiMethod));
    },

    _handleError: function (apiMethod, error) {
        if (apiMethod === this.apiErrorMethod) {
            return reason;
        }

        let errorReport = null;
        if (error.response) {
            let message = null;
            switch (error.response.status) {
                case 401:
                    message = 'You must log in to use this feature.';
                    break;
                case 403:
                    message = 'You are not authorized to use this feature.';
                    break;
                case 419:
                    message = 'Your browsing session has timed out. This usually happens when you leave the page open for a long time. Please refresh the page and try again.';
                    break;
                case this.apiValidationErrorStatusCode:
                    return Promise.reject(error); // Validation errors are pass-through.
                default:
                    errorReport = {
                        apiMethod,
                        data: error.response.data,
                        status: error.response.status,
                        headers: error.response.headers
                    };
                    break;
            }

            if (message !== null) {
                alert(message);
            }

        } else if (error.request) {
            // The request was made but no response was received
            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
            // http.ClientRequest in node.js
            errorReport = {
                apiMethod,
                request: error.request,
                error: 'API call received no response.'
            };
        } else {
            // Something happened in setting up the request that triggered an Error
            errorReport = {
                apiMethod, 
                error: 'API call failed to initialize. Error message: ' + error.message 
            };
        }

        if (errorReport !== null) {
            errorReport.config = error.config;
            this.error('API request failed', apiMethod, JSON.stringify(errorReport, undefined, 2));
        }

        return Promise.reject('API request failed ' + apiMethod);
    }
};

export default EDAPI;
