export interface IHistory {
    pushState(data: any, title: string, url: string): void;
}

export type UrlComponents = string | {
    queryString?: { [key: string]: string };
    path?: string;
};
