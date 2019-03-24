import classNames from 'classnames';
import React, {
    useEffect,
    useRef,
} from 'react';

import DateLabel from '@root/components/DateLabel';
import HtmlInject from '@root/components/HtmlInject';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import Avatar from './Avatar';
import { IProps } from './Post._types';
import ProfileLink from './ProfileLink';

export function Post(props: IProps) {
    const {
        post,
        renderToolbar,
    } = props;
    const {
        _isFocused: focused,
        _isThreadPost: isThreadPost,
    } = props.post;

    const postRef = useRef(null);

    useEffect(() => {
        if (focused) {
            makeVisibleInViewport(postRef.current);
        }
    }, [ focused, postRef ]);

    return <div className={classNames('forum-post', { 'forum-post--thread': isThreadPost })} ref={postRef}>
        <div className="post-profile-picture">
            <Avatar account={post.account} />
        </div>
        <div className="post-content">
            <div className="post-header">
                <ProfileLink account={post.account} className="nickname" />
                <a href={`?forum_post_id=${post.id}`} className="post-no">
                    #{post.id}
                </a>
            </div>
            <div className="post-body">
                {post.isDeleted
                    ? <em>{post.account.nickname} has redacted their post.</em>
                    : <HtmlInject html={post.content} />}
            </div>
            <div className="post-tools">
                <span className="date">
                    <DateLabel dateTime={post.createdAt} />
                </span>
                {renderToolbar && renderToolbar(props)}
            </div>
        </div>
    </div>;
}

export default Post;
