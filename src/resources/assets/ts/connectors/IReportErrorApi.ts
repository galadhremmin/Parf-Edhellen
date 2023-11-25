export const enum ErrorCategory {
    Backend = 'backend',
    Frontend = 'frontend',
    Timeout = 'timeout',
    RequestUnauthorized = 'http-401',
    RequestForbidden = 'http-403',
    SessionExpired = 'http-419',
    UnitTest = 'unit-test',
}

export interface IReportErrorApi {
    error(message: string, url: string, error: string, category?: ErrorCategory): Promise<void>;
}
