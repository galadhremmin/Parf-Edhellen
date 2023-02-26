import DateLabel from '@root/components/DateLabel';
import TextIcon from '@root/components/TextIcon';
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
        <div className="c text-end">
            {thread.numberOfPosts} <TextIcon icon="comment" />{' '}
            {thread.numberOfLikes} <TextIcon icon="thumbs-up" />
        </div>
    </div>;
}

export default DiscussTableRow;
