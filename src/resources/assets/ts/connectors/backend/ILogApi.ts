export interface IErrorEntity {
    accountId: number;
    category: string;
    createdAt: Date;
    error: string;
    id: number;
    ip: string;
    isCommon: boolean;
    message: string;
    updatedAt: Date;
    url: string;
    userAgent: string;
    sessionId: number;
    file: string;
    line: number;
    duration: number;
}

export interface IFailedJob {
    id: number;
    uuid: string;
    queue: string;
    payload: string;
    exception: string;
    failedAt: string;
}

export interface IAccountIpAddress {
    ip: string;
    lastSeen: string | null;
    numberOfOccurrences: number;
    sources: string[];
}

export interface IGetAccountIpHistoryResponse {
    accountId: number;
    ipAddresses: IAccountIpAddress[];
}

/**
 * An ag-Grid filter model, keyed by column field name. It is applied by the backend rather than
 * by the grid, so that it filters every record rather than just the loaded ones.
 */
export type IGridFilterModel = Record<string, unknown>;

export interface IGetErrorsRequest {
    /** Number of records to skip. */
    offset?: number;
    /** Maximum number of records to return. */
    limit?: number;
    category?: string;
    accountId?: number;
    ip?: string;
    filters?: IGridFilterModel;
}

export interface IGetErrorsResponse {
    errors: IErrorEntity[];
    length: number;
}

export interface IGetFailedJobsRequest {
    /** Number of records to skip. */
    offset?: number;
    /** Maximum number of records to return. */
    limit?: number;
}

export interface IGetFailedJobsResponse {
    errors: IFailedJob[];
    length: number;
}

export interface ILogApi {
    getErrors(args?: IGetErrorsRequest): Promise<IGetErrorsResponse>;
    getAccountIpHistory(accountId: number): Promise<IGetAccountIpHistoryResponse>;
    getFailedJobs(args?: IGetFailedJobsRequest): Promise<IGetFailedJobsResponse>;
    deleteError(id: number): Promise<void>;
    deleteErrorsByCategory(category: string, year?: number, week?: number): Promise<{ deleted: number }>;
}
