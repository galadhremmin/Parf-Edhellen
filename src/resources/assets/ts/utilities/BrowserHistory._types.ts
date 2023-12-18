export interface IHistory {
    pushState(data: any, title: string, url: string): void;
}

export type UrlComponents = string | {
    queryString?: { [key: string]: string };
    path?: string;
};

export interface IBrowserHistoryUtility {
    push<TData>(urlComponents: UrlComponents, title?: string, data?: TData | null): void;
    redirect(urlComponents: UrlComponents): void;
}
