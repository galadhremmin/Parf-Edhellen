import { ApiPath } from '@root/config';

import {
    FeedFormat,
    FeedUrlFactory,
} from './FeedApiConnector._types';

export const createFeedUrl: FeedUrlFactory = (context: 'discuss', entityType: 'posts',
    entityId: number = 0, feedFormat = FeedFormat.RSS) => //
    `${location.origin}${ApiPath}/${context}/feed/${entityType}/${entityId || ''}?format=${feedFormat}`;
