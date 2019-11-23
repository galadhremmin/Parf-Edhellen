import { FeedUrlFactory } from '@root/connectors/IFeedApi';

export interface IProps {
    feedUrlFactory: FeedUrlFactory;
    groupId?: number;
}
