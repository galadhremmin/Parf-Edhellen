import classNames from 'classnames';
import React, { useCallback } from 'react';

import { fireEventAsync } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';

import connectApi from './ApiConnector';
import { IProps } from './ApiConnector._types';
import './Likes.scss';

function Likes(props: IProps) {
    const {
        apiConnector,
        onThreadMetadataChange,
        post,
        thread,
        threadMetadata,
    } = props;

    const forumPostId = post.id;
    const forumThreadId = thread.id;
    const likedByAccount = threadMetadata.likes.indexOf(forumPostId) > -1;

    const _onLikeClick = useCallback(async (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        try {
            await apiConnector.likePost({
                forumPostId,
            });
            fireEventAsync(`Likes[${forumThreadId}-${forumPostId}]`, onThreadMetadataChange, {
                forumPostId: [ forumPostId ],
                forumThreadId,
            });
        } catch (e) {
            // failed
            console.warn(e);
        }
    }, [ apiConnector, forumPostId, forumThreadId, onThreadMetadataChange ]);

    return <a href={`?forum_post_id=${forumPostId}`} onClick={_onLikeClick}>
        {threadMetadata.likesPerPost[forumPostId] || 0}
        &nbsp;
        <TextIcon icon="thumbs-up" className={classNames({
            'likes--liked-by-user': likedByAccount,
        })} />
    </a>;
}

export default connectApi(Likes);
