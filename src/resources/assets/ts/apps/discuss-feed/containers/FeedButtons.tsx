import { createFeedUrl } from '@root/connectors/FeedApiConnector';

import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import { SecurityRole } from '@root/security';
import FeedButton from '../components/FeedButton';
import SubscribeButton from '../components/SubscribeButton';
import { IProps } from '../index._types';

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
} as Partial<IProps>;

export default withPropInjection(Feeds, {
    roleManager: DI.RoleManager,
});
