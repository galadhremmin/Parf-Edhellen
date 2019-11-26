export interface IErrorEntity {
    accountId: number;
    category: string;
    createdAt: Date;
    error: string;
    file: never;
    id: number;
    ip: string;
    isCommon: boolean;
    message: string;
    updatedAt: Date;
    url: string;
    userAgent: string;
}

export interface IGetErrorsResponse {
    currentPage: number;
    data: IErrorEntity[];
    firstPageUrl: number;
    from: number;
    lastPage?: number;
    nextPageUrl: number;
    path: string;
    perPage: number;
    prevPageUrl: string;
    to: number;
    total?: number;
}

export interface ILogApi {
    getErrors(page?: number): Promise<IGetErrorsResponse>;
}
