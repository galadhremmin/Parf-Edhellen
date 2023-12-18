import { useCallback } from 'react';

import { fireEventAsync } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import { withPropResolving } from '@root/di';
import { DI } from '@root/di/keys';

import { IProps } from './index._types';

import './Likes.scss';

function StickyPost(props: IProps) {
    const {
        apiConnector,
        onThreadChange,
    } = props;
    const {
        id,
        isSticky,
    } = props.thread;

    const _onStickyClick = useCallback(async () => {
        try {
            await apiConnector.stickThread({
                forumThreadId: id,
                sticky: ! isSticky,
            });

            fireEventAsync(`StickyPost[${id}]`, onThreadChange, id);
        } catch (ex) {
            alert(`Failed to apply stickiness: ${ex}`);
        }
    }, [
        apiConnector,
        id,
        isSticky,
        onThreadChange,
    ]);

    return <a href="#" onClick={_onStickyClick}>
        <TextIcon icon="pushpin" />{' '}
        {isSticky ? 'Unstick' : 'Stick'}
    </a>;
}

export default withPropResolving(StickyPost, {
    apiConnector: DI.DiscussApi,
});