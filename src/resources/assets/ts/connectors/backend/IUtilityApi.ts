export interface IMarkdownParserRequest {
    markdown?: string;
    markdowns?: string[];
}

export interface IMarkdownParserResponse {
    html: string;
}

export interface IGetErrorsResponse {
    errors: IErrorEntity;
}

export interface IErrorEntity {
    accountId: number;
    category: string;
    error: string;
    ip: string;
    isCommon: boolean;
    message: string;
    url: string;
}

export default interface IUtilityApi {
    parseMarkdown(args: IMarkdownParserRequest): Promise<IMarkdownParserResponse>;
    getErrors(): Promise<IGetErrorsResponse>;
}
