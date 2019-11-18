import { ApiPath } from '@root/config';

import {
    FeedFormat,
    FeedUrlFactory,
} from './IFeedApi';

export const createFeedUrl: FeedUrlFactory = (context: 'discuss', entityType: 'posts',
    entityId: number = 0, feedFormat = FeedFormat.RSS) => //
    `${location.origin}${ApiPath}/${context}/feed/${entityType}/${entityId || ''}?format=${feedFormat}`;
