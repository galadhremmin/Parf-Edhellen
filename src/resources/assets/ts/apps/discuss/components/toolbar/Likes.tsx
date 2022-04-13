import classNames from 'classnames';
import React, { useCallback, useEffect, useState } from 'react';

import { fireEventAsync } from '@root/components/Component';
import { SecurityRole } from '@root/config';
import TextIcon from '@root/components/TextIcon';
import useAnimationOnChange from '@root/utilities/hooks/useAnimationOnChange';

import connectApi from './ApiConnector';
import { IProps } from './ApiConnector._types';
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

    const forumPostId = post.id;
    const forumThreadId = thread.id;
    const likedByAccount = threadMetadata.likes.indexOf(forumPostId) > -1;
    const shouldAnimate = useAnimationOnChange(likedByAccount, 2000);

    const _onLikeClick = useCallback(async (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        try {
            const name = `Likes[${forumThreadId}-${forumPostId}]`;
            if (roleManager.currentRole === SecurityRole.Anonymous) {
                fireEventAsync(name, onAuthenticationRequired, 'like this post');
            } else {
                await apiConnector.likePost({
                    forumPostId,
                });
                fireEventAsync(name, onThreadMetadataChange, {
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
        forumPostId,
        forumThreadId,
        onAuthenticationRequired,
        onThreadMetadataChange,
        roleManager,
    ]);

    return <a href={`?forum_post_id=${forumPostId}`} onClick={_onLikeClick} className="Likes--link">
        {threadMetadata.likesPerPost[forumPostId] || 0}
        &nbsp;
        <TextIcon icon="thumbs-up" className={classNames({
            'filled': likedByAccount,
            'animate': shouldAnimate,
        })} />
    </a>;
}

export default connectApi(Likes);
