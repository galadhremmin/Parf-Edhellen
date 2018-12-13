export interface IMarkdownParserRequest {
    markdown?: string;
    markdowns?: string[];
}

export interface IMarkdownParserResponse {
    html: string;
}
