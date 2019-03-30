import classNames from 'classnames';
import React, { useCallback } from 'react';

import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import SharedReference from '@root/utilities/SharedReference';

import { IProps } from './Likes._types';
import './Likes.scss';

function Likes(props: IProps) {
    const {
        apiConnector,
        post,
        threadMetadata,
    } = props;

    const forumPostId = post.id;
    const likedByAccount = threadMetadata.likes.indexOf(forumPostId) > -1;

    const _onLikeClick = useCallback(async (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        const like = await apiConnector.like({
            forumPostId,
        });
    }, [ apiConnector, forumPostId ]);

    return <a href={`?forum_post_id=${forumPostId}`} onClick={_onLikeClick}>
        {threadMetadata.likesPerPost[forumPostId] || 0}
        &nbsp;
        <span className={classNames('glyphicon', 'glyphicon-thumbs-up', {
            'likes--liked-by-user': likedByAccount,
        })} />
    </a>;
}

Likes.defaultProps = {
    apiConnector: SharedReference.getInstance(DiscussApiConnector),
} as Partial<IProps>;

export default Likes;
