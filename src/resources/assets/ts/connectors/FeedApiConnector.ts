import { ApiPath } from '@root/config';

import { FeedFormat } from './FeedApiConnector._types';

export const createFeedUrl = (context: 'discuss', entityType: 'posts', entityId: number = 0, feedFormat = FeedFormat.RSS) => {
    return `${location.origin}${ApiPath}/${context}/feed/${entityType}/${entityId || ''}?format=${feedFormat}`;
};
