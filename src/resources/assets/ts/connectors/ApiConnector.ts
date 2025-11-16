/* eslint-disable @typescript-eslint/unbound-method */
import Cookies from 'js-cookie';

import {
    ApiExceptionCollectorMethod,
    ApiPath,
    ApiValidationFailedStatusCode,
} from '../config';
import {
    propsToSnakeCase,
    snakeCasePropsToCamelCase,
    toSnakeCase,
} from '../utilities/func/snake-case';
import type {
    IApiBaseConnector,
    IErrorReport,
    IQueryStringMap,
    IValidationFailedResponse,
    FetchRequestConfig,
    IConnectorError,
} from './ApiConnector._types';
import {
    ErrorCategory,
    type IReportErrorApi,
} from './IReportErrorApi';
import ValidationError from './ValidationError';
import { ApiTimeoutInMilliseconds } from '../config';

export default class ApiConnector implements IApiBaseConnector, IReportErrorApi {
    private _abortController: AbortController;
    
    constructor(
        private _apiPathName: string = ApiPath,
        private _apiErrorMethod: string = ApiExceptionCollectorMethod,
        private _apiValidationErrorStatusCode: number = ApiValidationFailedStatusCode) {
        this._abortController = new AbortController();
        window.addEventListener('beforeunload', () => {
            this._abortController.abort();
        });
    }

    /**
     * Execute a DELETE request.
     */
    public delete<T>(apiMethod: string, queryStringMap: IQueryStringMap = null) {
        return this._consume<T>(apiMethod, this.deleteRaw<T>(apiMethod, queryStringMap));
    }

    /**
     * Execute a DELETE request and returns the request object.
     */
    public deleteRaw<T = any>(apiMethod: string, queryStringMap: IQueryStringMap = null) {
        return this._createRequest<T>('DELETE', apiMethod, queryStringMap);
    }

    /**
     * Execute a HEAD request.
     */
    public head<T>(apiMethod: string, queryStringMap: IQueryStringMap = null) {
        return this._consume<T>(apiMethod, this.headRaw<T>(apiMethod, queryStringMap));
    }

    /**
     * Execute a HEAD request.
     */
    public headRaw<T = any>(apiMethod: string, queryStringMap: IQueryStringMap = null) {
        return this._createRequest<T>('HEAD', apiMethod, queryStringMap);
    }

    /**
     * Execute a GET request.
     */
    public get<T>(apiMethod: string, queryStringMap: IQueryStringMap = null) {
        return this._consume<T>(apiMethod, this.getRaw<T>(apiMethod, queryStringMap));
    }

    /**
     * Execute a GET request.
     */
    public getRaw<T = any>(apiMethod: string, queryStringMap: IQueryStringMap = null) {
        return this._createRequest<T>('GET', apiMethod, queryStringMap);
    }

    /**
     * Execute a POST request.
     */
    public post<T>(apiMethod: string, payload: any, queryStringMap: IQueryStringMap = null) {
        return this._consume<T>(apiMethod, this.postRaw<T>(apiMethod, payload || {}, queryStringMap));
    }

    /**
     * Execute a POST request.
     */
    public postRaw<T = any>(apiMethod: string, payload: any, queryStringMap: IQueryStringMap = null) {
        return this._createRequest<T>('POST', apiMethod, queryStringMap, payload || {});
    }

    /**
     * Execute a PUT request.
     */
    public put<T>(apiMethod: string, payload: any, queryStringMap: IQueryStringMap = null) {
        return this._consume<T>(apiMethod, this.putRaw<T>(apiMethod, payload || {}, queryStringMap));
    }

    /**
     * Execute a PUT request.
     */
    public putRaw<T = any>(apiMethod: string, payload: any, queryStringMap: IQueryStringMap = null) {
        return this._createRequest<T>('PUT', apiMethod, queryStringMap, payload || {});
    }

    /**
     * Register the specified error.
     */
    public error(message: string, url: string, error: string, category: ErrorCategory = null, duration: number = null) {
        return this.post<void>(this._apiErrorMethod, {
            message,
            url,
            error,
            category,
            duration,
        });
    }

    /**
     * Default XMLHTTPRequest configuration.
     */
    public get config() {
        const cfg: FetchRequestConfig = {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN'),
                'X-Requested-With': 'XMLHttpRequest',
            },
            timeout: ApiTimeoutInMilliseconds,
            withCredentials: true,
            signal: this._abortController.signal,
        };
        return cfg;
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

    private _prepareUrl(methodName: string, queryStringMap: IQueryStringMap) {
        let url = this._absPath(methodName);

        if (queryStringMap !== null) {
            const keyValuePairs = Object.keys(queryStringMap).reduce((carry: string[], key: string) => [
                ...carry,
                `${toSnakeCase(key)}=${encodeURIComponent(
                    typeof queryStringMap[key] === 'object' 
                        ? JSON.stringify(queryStringMap[key])
                        // eslint-disable-next-line @typescript-eslint/no-base-to-string
                        : String(queryStringMap[key]))}`,
            ], []);

            if (keyValuePairs.length > 0) {
                url += '?' + keyValuePairs.join('&');
            }
        }

        return url;
    }

    private _nodeGuard() {
        
    }

    private _createRequest<T = any>(method: string, apiMethod: string, queryStringMap: IQueryStringMap,
        payload: any = null): Promise<Response> {
        if (! apiMethod || apiMethod.length < 1) {
            return Promise.reject(new Error('You need to specify an API method to invoke.'));
        }

        this._nodeGuard();

        const url = this._prepareUrl(apiMethod, queryStringMap);
        const config: RequestInit = {
            method,
            headers: { ...(this.config.headers || {}) },
            credentials: this.config.withCredentials ? 'include' : 'same-origin',
            signal: this.config.signal,
        };
        const hasBody = payload !== null;
        if (hasBody) {
            if (payload instanceof FormData) {
                // Let the browser set the multipart boundary
            } else {
                config.headers = { ...config.headers, 'Content-Type': 'application/json' };
                payload = propsToSnakeCase(payload);
            }
            config.body = payload instanceof FormData ? payload : JSON.stringify(payload);
        }

        return fetch(url, config);
    }

    private async _consume<T>(apiMethod: string, request: Promise<Response>): Promise<T> {
        this._nodeGuard();

        // Record start time for performance measurement
        const startTime = performance.now();

        try {
            const response = await request;
            if (! response) {
                return undefined as unknown as T;
            }
            if (! response.ok) {
                // Simulate axios-like error handling via throwing with status
                const error: IConnectorError = Object.assign(new Error(`HTTP ${response.status}`), {
                    name: 'HttpError',
                    response: {
                        status: response.status,
                        headers: Object.fromEntries(response.headers.entries()),
                        data: await this._safeParseJson(response),
                    },
                    config: {},
                    request: null,
                });

                throw error;
            }
            const data = await this._safeParseJson(response);
            return snakeCasePropsToCamelCase(data);
        } catch (error: unknown) {
            const duration = performance.now() - startTime;
            return this._handleError(apiMethod, error as IConnectorError | Error, duration);
        }
    }

    private async _handleError(apiMethod: string, error: IConnectorError | Error, duration?: number) {
        if (error === undefined) {
            console.warn(`Received an empty error from ${apiMethod}.`);
        }

        if (apiMethod === this._apiErrorMethod) {
            return Promise.reject(error instanceof Error ? error : new Error('Unknown error'));
        }

        const requestWasCanceled = (error as IConnectorError)?.name === 'AbortError' ||
            (error as IConnectorError)?.code === 'ECONNABORTED' ||
            (duration || 0) >= ApiTimeoutInMilliseconds;

        let errorReport: IErrorReport = null;
        let category: ErrorCategory;
        if (requestWasCanceled) {
            console.warn('Your request timed out. This is likely due to us failing to respond to your request in time. Please try to reload the page and try again.');
            // We don't need to record cancellations.
            errorReport = null;

        } else if ((error as IConnectorError).response) {
            const response = (error as IConnectorError).response;
            let message = null;
            switch (response.status) {
                case 401:
                    message = 'You must sign in to use this feature.';
                    category = ErrorCategory.RequestUnauthorized;
                    break;
                case 403:
                    message = 'You are not authorized to use this feature.';
                    category = ErrorCategory.RequestForbidden;
                    break;
                case 404:
                    return Promise.reject(new Error(`${apiMethod}: The specified resource cannot be found.`));
                case 419:
                    message = 'Your browsing session has timed out. This usually happens when you leave ' +
                        'the page open for a long time. Please refresh the page and try again.';
                    category = ErrorCategory.SessionExpired;
                    break;
                case this._apiValidationErrorStatusCode:
                    return Promise.reject(new ValidationError(
                        (response.data as IValidationFailedResponse)?.message,
                        (response.data as IValidationFailedResponse)?.errors,
                    ));
                default:
                    errorReport = {
                        apiMethod,
                        data: response.data,
                        headers: response.headers,
                        status: response.status,
                    };
                    break;
            }

            if (message !== null) {
                alert(message);
            }

        } else if ((error as IConnectorError).request) {
            errorReport = {
                apiMethod,
                error: 'API call received no response.',
            };
            category = ErrorCategory.Empty;
        } else {
            // No response object. Distinguish offline client vs server not responding.
            const isOffline = typeof window === 'object' &&
                typeof navigator === 'object' &&
                (navigator as Navigator).onLine === false;
            if (isOffline) {
                // Do not record client offline incidents; nothing actionable server-side.
                return Promise.reject(error instanceof Error ? error : new Error('Network offline'));
            }
            
            // Something happened in setting up the request that triggered an Error
            errorReport = {
                apiMethod,
                error: `API call failed to initialize. Error message: ${(error as Error).message || 'Unknown error'}`,
            };
            category = ErrorCategory.Frontend;
        }

        if (errorReport !== null) {
            errorReport.config = (error as IConnectorError).config;
            await this.error(
                'API request failed', 
                apiMethod, 
                JSON.stringify(errorReport, undefined, 2),
                category,
                duration,
            );
        }

        return Promise.reject(new Error(`API request failed ${apiMethod}`));
    }

    private async _safeParseJson(res: Response) {
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            try {
                return await res.json();
            } catch {
                return null;
            }
        }
        try {
            return await res.text();
        } catch {
            return null;
        }
    }
}
