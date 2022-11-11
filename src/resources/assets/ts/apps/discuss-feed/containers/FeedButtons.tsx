import React from 'react';

import { DI, resolve } from '@root/di';
import { createFeedUrl } from '@root/connectors/FeedApiConnector';

import SubscribeButton from '../components/SubscribeButton';
import { IProps } from '../index._types';
import FeedButton from '../components/FeedButton';

function Feeds({
    groupId,
    feedUrlFactory,
    subscriptionApi,
}: IProps) {
    return <div className="text-end">
        <SubscribeButton className="me-2" groupId={groupId} subscriptionApi={subscriptionApi} />
        <FeedButton feedUrlFactory={feedUrlFactory} groupId={groupId}  />
    </div>;
}

Feeds.defaultProps = {
    feedUrlFactory: createFeedUrl,
    subscriptionApi: resolve(DI.SubscriptionApi),
} as Partial<IProps>;

export default Feeds;
