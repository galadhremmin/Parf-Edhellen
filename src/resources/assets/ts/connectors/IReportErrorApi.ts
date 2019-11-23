export const enum ErrorCategory {
    Backend = 'backend',
    Frontend = 'frontend',
}

export interface IReportErrorApi {
    error(message: string, url: string, error: string, category?: ErrorCategory): Promise<void>;
}
