import { createFeedUrl } from '@root/connectors/FeedApiConnector';

import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import FeedButton from '../components/FeedButton';
import SubscribeButton from '../components/SubscribeButton';
import type { IProps } from '../index._types';

function Feeds({
    groupId,
    feedUrlFactory = createFeedUrl,
    roleManager,
    subscriptionApi,
}: IProps) {
    return <div className="text-end">
        {! roleManager.isAnonymous && <SubscribeButton className="me-2" groupId={groupId} subscriptionApi={subscriptionApi} />}
        <FeedButton feedUrlFactory={feedUrlFactory} groupId={groupId}  />
    </div>;
}

export default withPropInjection(Feeds, {
    roleManager: DI.RoleManager,
    subscriptionApi: DI.SubscriptionApi,
});
