import { ApiPath } from '@root/config';

import {
    FeedFormat,
    type FeedUrlFactory,
} from './IFeedApi';

export const createFeedUrl: FeedUrlFactory = (context: 'discuss', entityType: 'posts',
    entityId = 0, feedFormat = FeedFormat.RSS) => //
    `${location.origin}${ApiPath}/${context}/feed/${entityType}/${entityId || ''}?format=${feedFormat}`;
