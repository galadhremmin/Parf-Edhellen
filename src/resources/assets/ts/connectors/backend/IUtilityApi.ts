export interface IMarkdownParserRequest {
    markdown?: string;
    markdowns?: string[];
}

export interface IMarkdownParserResponse {
    html: string;
}

export default interface IUtilityApi {
    parseMarkdown(args: IMarkdownParserRequest): Promise<IMarkdownParserResponse>;
}
