import classNames from 'classnames';
import React, { useCallback } from 'react';

import { fireEventAsync } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import useAnimationOnChange from '@root/utilities/hooks/useAnimationOnChange';

import { IProps } from './index._types';

import './Likes.scss';

function Likes(props: IProps) {
    const {
        apiConnector,
        onAuthenticationRequired,
        onThreadMetadataChange,
        post,
        roleManager,
        thread,
        threadMetadata,
    } = props;

    const {
        entityId,
        entityType,
    } = thread;

    const forumPostId = post.id;
    const forumThreadId = thread.id;
    const likedByAccount = threadMetadata.likes.indexOf(forumPostId) > -1;
    const shouldAnimate = useAnimationOnChange(likedByAccount, 2000);

    const _onLikeClick = useCallback(async (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        try {
            const name = `Likes[${forumThreadId}-${forumPostId}]`;
            if (roleManager.isAnonymous) {
                void fireEventAsync(name, onAuthenticationRequired, 'like this post');
            } else {
                await apiConnector.likePost({
                    forumPostId,
                });
                void fireEventAsync(name, onThreadMetadataChange, {
                    entityId,
                    entityType,
                    forumPostId: [ forumPostId ],
                    forumThreadId,
                });
            }
        } catch (e) {
            // failed
            console.warn(e);
            alert(`Sorry! For some reason, we failed to like the post. System error: ${e}!`);
        }
    }, [
        apiConnector,
        entityId,
        entityType,
        forumPostId,
        forumThreadId,
        onAuthenticationRequired,
        onThreadMetadataChange,
        roleManager,
    ]);

    return <a href={`?forum_post_id=${forumPostId}`} onClick={() => void _onLikeClick} className="Likes--link">
        {threadMetadata.likesPerPost[forumPostId] || 0}
        &nbsp;
        <TextIcon icon="thumbs-up" className={classNames({
            'filled': likedByAccount,
            'animate': shouldAnimate,
        })} />
    </a>;
}

export default withPropInjection(Likes, {
    apiConnector: DI.DiscussApi,
});
