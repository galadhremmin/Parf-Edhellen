import { ISubscriptionApi } from '@root/connectors/backend/ISubscriptionApi';
import { FeedUrlFactory } from '@root/connectors/IFeedApi';

export interface IProps {
    feedUrlFactory: FeedUrlFactory;
    groupId?: number;
    subscriptionApi: ISubscriptionApi; 
}
