export const enum ErrorCategory {
    Backend = 'backend',
    Frontend = 'frontend',
    Empty = 'empty-response',
    RequestUnauthorized = 'http-401',
    RequestForbidden = 'http-403',
    SessionExpired = 'http-419',
    UnitTest = 'unit-test',
    Performance = 'performance',
}

export interface IReportErrorApi {
    error(message: string, url: string, error: string, category?: ErrorCategory): Promise<void>;
}
