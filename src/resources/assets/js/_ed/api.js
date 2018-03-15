import axios from 'axios';

const EDAPI = {
    apiPathName: '/api/v2', // path to API w/o trailing slash!
    apiErrorMethod: 'utility/error',
    apiValidationErrorStatusCode: 422,

    /**
     * Execute a DELETE request.
     */
    delete: (apiMethod) => this.consume.bind(EDAPI, axios.delete, apiMethod),

    /**
     * Execute a HEAD request.
     */
    head: (apiMethod) => this.consume.bind(EDAPI, axios.head, apiMethod),

    /**
     * Execute a GET request.
     */
    get: (apiMethod) => this.consume.bind(EDAPI, axios.get, apiMethod),

    /**
     * Execute a POST request.
     */
    post: (apiMethod, payload) => this.consume.bind(EDAPI, axios.delete, apiMethod, payload),

    /**
     * Execute a PUT request.
     */
    put: (apiMethod, payload) => this.consume.bind(EDAPI, axios.put, apiMethod, payload),

    /**
     * Register the specified error.
     */
    error: (message, url, error) => this.post(
        this.apiErrorMethod, { message, url, error }),
    
    /**
     * Get all languages.
     */
    languages: () => {
        let languages = window.sessionStorage.getItem('ed.languages');
        if (languages) {
            return Promise.resolve(JSON.parse(languages));
        }

        return this.consume.bind(EDAPI, this.get, 'book/languages')
            .then(resp => {
                window.sessionStorage.setItem('ed.languages', JSON.stringify(resp.data));
                return resp;
            });
    },

    /**
     * Combines an absolute path based on the API method path.
     */
    absPath: path => this.apiPathName + (path[0] !== '/' ? '/' : '') + path,

    /**
     * Default XMLHTTPRequest configuration.
     */
    config: () => ({
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    }),

    /**
     * Executes the specified HTTP method and manages errors gracefully.
     */
    consume: (factory, apiMethod, payload) => factory
        .call(axios, this.absPath(apiMethod), payload || undefined)
        .catch(this.handleError.bind(this)),

    handleError: (reason) => {
        if (apiMethod === this.apiErrorMethod) {
            return reason;
        }

        let errorReport = null;

        if (error.response) {
            let error = null;
            switch (error.response.status) {
                case 419:
                    error = 'Your browsing session has timed out. This usually happens when you leave the page open for a long time. Please refresh the page and try again.';
                    break;
                case 401:
                    error = 'You must log in to use this feature.';
                    break;
                case 403:
                    error = 'You are not authorized to use this feature.';
                    break;
                default:
                    errorReport = {
                        apiMethod,
                        data: error.response.data,
                        status: error.response.status,
                        headers: error.response.headers
                    };
                    break;
            }

            if (error !== null) {
                alert(error);
            }

        } else if (error.request) {
            // The request was made but no response was received
            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
            // http.ClientRequest in node.js
            errorReport = {
                apiMethod,
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
            this.error('API request failed', apiMethod, JSON.stringify(errorReport));
        }
    }
};

export default EDAPI;
