import { ILogApi } from './ILogApi';

export interface IMarkdownParserRequest {
    markdown?: string;
    markdowns?: string[];
}

export interface IMarkdownParserResponse {
    html: string;
}

export default interface IUtilityApi extends ILogApi {
    parseMarkdown(args: IMarkdownParserRequest): Promise<IMarkdownParserResponse>;
}
