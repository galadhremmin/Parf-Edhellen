import React from 'react';

import HtmlInject from '@root/components/HtmlInject';
import Avatar from './Avatar';
import ProfileLink from './ProfileLink';

import { IPostEntity } from '@root/connectors/backend/DiscussApiConnector._types';
import { IProps } from './Post._types';

export default class Post extends React.PureComponent<IProps> {
    public render() {
        const {
            post,
        } = this.props;

        return <div className="forum-post" id={`forum-post-${post.id}`}>
            <div className="post-profile-picture">
                <Avatar account={post.account} />
            </div>
            <div className="post-content">
                <div className="post-header">
                    <ProfileLink account={post.account} className="nickname" />
                    <span className="post-no">#{post.id}</span>
                </div>
                <div className="post-body">
                    {this._renderContent(post)}
                </div>
                <div className="post-tools">
                    <span className="date">{post.createdAt}</span>
                </div>
            </div>
        </div>;
    }

    private _renderContent(post: IPostEntity) {
        return post.isDeleted
            ? <em>{post.account.nickname} has redacted their post.</em>
            : <HtmlInject html={post.content} />;
    }
}
