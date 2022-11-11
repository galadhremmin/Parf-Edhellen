import { ISubscriptionApi } from '@root/connectors/backend/ISubscriptionApi';
import { FeedUrlFactory } from '@root/connectors/IFeedApi';
import { IRoleManager } from '@root/security';

export interface IProps {
    feedUrlFactory: FeedUrlFactory;
    groupId?: number;
    roleManager: IRoleManager;
    subscriptionApi: ISubscriptionApi; 
}
