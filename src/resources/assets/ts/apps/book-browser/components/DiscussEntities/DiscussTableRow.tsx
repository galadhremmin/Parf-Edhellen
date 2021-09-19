import DateLabel from '@root/components/DateLabel';
import React from 'react';

import { IProps } from './DiscussTableRow._types';

function DiscussTableRow(props: IProps) {
    const {
        thread,
    } = props;

    return <div className="r">
        <div className="c">
            <a href={thread.accountPath} title={`View ${thread.accountName} profile`} className="pp">
                <img src={thread.accountAvatarPath} />
            </a>
        </div>
        <div className="c p2">
            <a href={thread.threadPath}>{thread.subject}</a>
            <div className="pi">
                {thread.accountName} on <DateLabel dateTime={thread.updatedAt || thread.createdAt} />
            </div>
        </div>
        <div className="c text-right">
            {thread.numberOfPosts} <span className="glyphicon glyphicon-comment"></span>{' '}
            {thread.numberOfLikes} <span className="glyphicon glyphicon-thumbs-up"></span>
        </div>
    </div>;
}

export default DiscussTableRow;
