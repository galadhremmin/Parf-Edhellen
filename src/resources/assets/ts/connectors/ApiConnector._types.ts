export interface FetchRequestConfig {
    headers?: Record<string, string>;
    timeout?: number;
    withCredentials?: boolean;
    signal?: AbortSignal;
    method?: string;
}

export interface IValidationFailedResponse {
    message: string;
    errors: Record<string, string[]>;
}

export interface IErrorReport {
    apiMethod?: string;
    config?: any;
    data?: any;
    duration?: number;
    error?: string;
    headers?: any;
    status?: number;
}

export interface IQueryStringMap {
    [key: string]: unknown;
}

export interface IApiBaseConnector {
    /**
     * Execute a DELETE request.
     */
    delete<T>(apiMethod: string, queryStringMap?: IQueryStringMap): Promise<T>;

    /**
     * Execute a DELETE request and returns the request object.
     */
    deleteRaw<T = any>(apiMethod: string, queryStringMap?: IQueryStringMap): Promise<Response>;

    /**
     * Execute a HEAD request.
     */
    head<T>(apiMethod: string, queryStringMap?: IQueryStringMap): Promise<T>;

    /**
     * Execute a HEAD request.
     */
    headRaw<T = any>(apiMethod: string, queryStringMap?: IQueryStringMap): Promise<Response>;

    /**
     * Execute a GET request.
     */
    get<T>(apiMethod: string, queryStringMap?: IQueryStringMap): Promise<T>;

    /**
     * Execute a GET request.
     */
    getRaw<T = any>(apiMethod: string, queryStringMap?: IQueryStringMap): Promise<Response>;

    /**
     * Execute a POST request.
     */
    post<T>(apiMethod: string, payload: any, queryStringMap?: IQueryStringMap): Promise<T>;

    /**
     * Execute a POST request.
     */
    postRaw<T = any>(apiMethod: string, payload: any, queryStringMap?: IQueryStringMap): Promise<Response>;

    /**
     * Execute a PUT request.
     */
    put<T>(apiMethod: string, payload: any, queryStringMap?: IQueryStringMap): Promise<T>;

    /**
     * Execute a PUT request.
     */
    putRaw<T = any>(apiMethod: string, payload: any, queryStringMap?: IQueryStringMap): Promise<Response>;

    /**
     * Default XMLHTTPRequest configuration.
     */
    get config(): FetchRequestConfig;
}
