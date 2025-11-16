import type { ISubscriptionApi } from '@root/connectors/backend/ISubscriptionApi';
import type { FeedUrlFactory } from '@root/connectors/IFeedApi';
import type { IRoleManager } from '@root/security';

export interface IProps {
    feedUrlFactory: FeedUrlFactory;
    groupId?: number;
    roleManager: IRoleManager;
    subscriptionApi: ISubscriptionApi; 
}
