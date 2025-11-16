import classNames from 'classnames';
import { useEffect, useState } from 'react';

import TextIcon from '@root/components/TextIcon';

import type { IProps } from './SubscribeButton._types';

const ForumGroupEntityName = 'forum_group';

function SubscribeButton({
    className,
    groupId,
    subscriptionApi,
}: IProps) {
    const [ isSubscribed, setIsSubscribed ] = useState<boolean | null>(null);

    useEffect(() => {
        subscriptionApi.isSubscribed(ForumGroupEntityName, groupId) //
            .then((response) => setIsSubscribed(response.subscribed)) // 
            .catch(() => setIsSubscribed(false)); // If the request fails, assume not subscribed
    }, [ groupId ]);

    const _onSwapSubscription = () => {
        let response;
        if (isSubscribed) {
            response = subscriptionApi.unsubscribe(ForumGroupEntityName, groupId);
        } else {
            response = subscriptionApi.subscribe(ForumGroupEntityName, groupId);
        }

        response.then((r) => setIsSubscribed(r.subscribed))
            .catch(() => {}); // If the request fails, do nothing
    };

    return isSubscribed !== null ? //
        <button className={classNames('btn btn-sm btn-secondary', className)}
            title={isSubscribed ? 'You are subscribed. Press to unsubscribe.' : 'You are not subscribed. Press to subscribe.'}
            onClick={_onSwapSubscription}>
            <TextIcon icon="bell" className={classNames({ 'filled': isSubscribed })} />
            &#32;
            { isSubscribed ? 'Subscribed' : 'Subscribe' }
        </button> : null;
}

export default SubscribeButton;
