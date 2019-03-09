import React, {
    useEffect,
    useRef,
} from 'react';

import HtmlInject from '@root/components/HtmlInject';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import Avatar from './Avatar';
import ProfileLink from './ProfileLink';
import { IProps } from './Post._types';

export function Post(props: IProps) {
    const {
        post,
    } = props;
    const {
        _focused: focused,
    } = props.post;

    const postRef = useRef(null);

    useEffect(() => {
        if (focused) {
            makeVisibleInViewport(postRef.current);
        }
    }, [ focused, postRef ]);

    return <div className="forum-post" id={`forum-post-${post.id}`} ref={postRef}>
        <div className="post-profile-picture">
            <Avatar account={post.account} />
        </div>
        <div className="post-content">
            <div className="post-header">
                <ProfileLink account={post.account} className="nickname" />
                <span className="post-no">#{post.id}</span>
            </div>
            <div className="post-body">
                {post.isDeleted
                    ? <em>{post.account.nickname} has redacted their post.</em>
                    : <HtmlInject html={post.content} />}
            </div>
            <div className="post-tools">
                <span className="date">{post.createdAt}</span>
            </div>
        </div>
    </div>;
}

export default Post;
