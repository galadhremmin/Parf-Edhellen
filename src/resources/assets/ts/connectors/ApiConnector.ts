import axios, {
    AxiosError,
    AxiosInstance,
    AxiosPromise,
    AxiosResponse,
} from 'axios';

interface IErrorReport {
    apiMethod?: string;
    config?: any;
    data?: any;
    error?: string;
    headers?: any;
    status?: number;
}

export default class ApiConnector {
    constructor(
        private _apiPathName: string,
        private _apiErrorMethod: string,
        private _apiValidationErrorStatusCode: number,
        private _factory: AxiosInstance = axios) {
    }

    /**
     * Execute a DELETE request.
     */
    public delete<T>(apiMethod: string) {
        return this._consume<T>(apiMethod, this.deleteRaw(apiMethod));
    }

    /**
     * Execute a DELETE request and returns the request object.
     */
    public deleteRaw(apiMethod: string) {
        return this._createRequest(this._factory.delete, apiMethod);
    }

    /**
     * Execute a HEAD request.
     */
    public head<T>(apiMethod: string) {
        return this._consume<T>(apiMethod, this.headRaw(apiMethod));
    }

    /**
     * Execute a HEAD request.
     */
    public headRaw(apiMethod: string) {
        return this._createRequest(this._factory.head, apiMethod);
    }

    /**
     * Execute a GET request.
     */
    public get<T>(apiMethod: string) {
        return this._consume<T>(apiMethod, this.getRaw(apiMethod));
    }

    /**
     * Execute a GET request.
     */
    public getRaw(apiMethod: string) {
        return this._createRequest(this._factory.get, apiMethod);
    }

    /**
     * Execute a POST request.
     */
    public post<T>(apiMethod: string, payload: any) {
        return this._consume<T>(apiMethod, this.postRaw(apiMethod, payload || {}));
    }

    /**
     * Execute a POST request.
     */
    public postRaw(apiMethod: string, payload: any) {
        return this._createRequest(this._factory.post, apiMethod, payload || {});
    }

    /**
     * Execute a PUT request.
     */
    public put<T>(apiMethod: string, payload: any) {
        return this._consume<T>(apiMethod, this.putRaw(apiMethod, payload || {}));
    }

    /**
     * Execute a PUT request.
     */
    public putRaw(apiMethod: string, payload: any) {
        return this._createRequest(this._factory.put, apiMethod, payload || {});
    }

    /**
     * Register the specified error.
     */
    public error(message: string, url: string, error: string, category: string = 'frontend') {
        return this.post(this._apiErrorMethod, { message, url, error, category });
    }

    /**
     * Default XMLHTTPRequest configuration.
     */
    public get config() {
        return {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            timeout: 2500,
        };
    }

    /**
     * Combines an absolute path based on the API method path.
     */
    private _absPath(path: string) {
        if (typeof window === 'object') {
            const origin = window.location.origin;
            if (origin.length < path.length && path.substr(0, origin.length) === origin) {
                return path;
            }
        }

        return path[0] === '/' ? path : this._apiPathName + '/' + path;
    }

    private async _consume<T>(apiMethod: string, request: AxiosPromise<AxiosResponse<T>>): Promise<T> {
        try {
            const response = await request;
            return response.data as any;
        } catch (error) {
            return this._handleError(apiMethod, error);
        }
    }

    private _createRequest(factory: any, apiMethod: string, payload: any = null): AxiosPromise<AxiosResponse> {
        if (! apiMethod || apiMethod.length < 1) {
            return Promise.reject(`You need to specify an API method to invoke.`);
        }

        const config = this.config;
        const hasBody = payload !== null;
        return factory.call(this._factory, this._absPath(apiMethod),
            hasBody ? payload : config,
            hasBody ? config : undefined,
        );
    }

    private _handleError(apiMethod: string, error: AxiosError) {
        if (apiMethod === this._apiErrorMethod) {
            return Promise.reject(error);
        }

        let errorReport: IErrorReport = null;
        let category: string;
        if (error.response) {
            let message = null;
            switch (error.response.status) {
                case 401:
                    message = 'You must log in to use this feature.';
                    category = 'frontend-401';
                    break;
                case 403:
                    message = 'You are not authorized to use this feature.';
                    category = 'frontend-403';
                    break;
                case 419:
                    message = 'Your browsing session has timed out. This usually happens when you leave ' +
                        'the page open for a long time. Please refresh the page and try again.';
                    category = 'frontend-419';
                    break;
                case this._apiValidationErrorStatusCode:
                    return Promise.reject(error); // Validation errors are pass-through.
                default:
                    errorReport = {
                        apiMethod,
                        data: error.response.data,
                        headers: error.response.headers,
                        status: error.response.status,
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
            //
            // 180602: This type of error is non-actionable.
            errorReport = null;

            /*
            errorReport = {
                apiMethod,
                request: error.request,
                error: 'API call received no response.'
            };
            category = 'api-noresponse';
            */
        } else {
            // Something happened in setting up the request that triggered an Error
            errorReport = {
                apiMethod,
                error: 'API call failed to initialize. Error message: ' + error.message,
            };
        }

        if (errorReport !== null) {
            errorReport.config = error.config;
            this.error('API request failed', apiMethod, JSON.stringify(errorReport, undefined, 2), category);
        }

        return Promise.reject('API request failed ' + apiMethod);
    }
}
