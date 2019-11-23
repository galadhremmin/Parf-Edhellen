export enum FeedFormat {
    JSON = 'json',
    RSS = 'rss',
}

export type FeedUrlFactory = (context: string, entityType: string, entityId: number, feedFormat: FeedFormat) => string;
