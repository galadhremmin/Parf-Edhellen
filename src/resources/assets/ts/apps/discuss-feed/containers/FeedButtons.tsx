import { DI, resolve } from '@root/di';
import { createFeedUrl } from '@root/connectors/FeedApiConnector';

import SubscribeButton from '../components/SubscribeButton';
import { IProps } from '../index._types';
import FeedButton from '../components/FeedButton';
import { SecurityRole } from '@root/security';

function Feeds({
    groupId,
    feedUrlFactory,
    roleManager,
    subscriptionApi,
}: IProps) {
    const isAuthenticated = roleManager.currentRole !== SecurityRole.Anonymous;
    return <div className="text-end">
        {isAuthenticated && <SubscribeButton className="me-2" groupId={groupId} subscriptionApi={subscriptionApi} />}
        <FeedButton feedUrlFactory={feedUrlFactory} groupId={groupId}  />
    </div>;
}

Feeds.defaultProps = {
    feedUrlFactory: createFeedUrl,
    roleManager: resolve(DI.RoleManager),
    subscriptionApi: resolve(DI.SubscriptionApi),
} as Partial<IProps>;

export default Feeds;
