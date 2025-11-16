import type { FeedUrlFactory } from '@root/connectors/IFeedApi';

import type { IProps as IParentProps } from './SubscribeButton._types';

export interface IProps extends Omit<IParentProps, 'subscriptionApi'> {
    feedUrlFactory: FeedUrlFactory;
}
