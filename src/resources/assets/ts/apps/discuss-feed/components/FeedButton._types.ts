import { FeedUrlFactory } from '@root/connectors/IFeedApi';

import { IProps as IParentProps } from './SubscribeButton._types';

export interface IProps extends Omit<IParentProps, 'subscriptionApi'> {
    feedUrlFactory: FeedUrlFactory;
}
