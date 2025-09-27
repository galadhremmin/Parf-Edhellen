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

export interface IGetErrorsResponse {
    errors: IErrorEntity[];
    length: number;
}

export interface IGetFailedJobsResponse {
    errors: IFailedJob[];
    length: number;
}

export interface ILogApi {
    getErrors(from?: number, to?: number): Promise<IGetErrorsResponse>;
    getFailedJobs(from?: number, to?: number): Promise<IGetFailedJobsResponse>;
}
