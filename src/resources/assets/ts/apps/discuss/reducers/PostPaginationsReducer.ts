import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import PostPaginationReducer from './PostPaginationReducer';

export default collectivize(
    PostPaginationReducer,
    (e, a) => e.forumThreadId === (a.threadData.threadId ?? a.threadData.thread.id),
    [
        Actions.ReceiveThread,
    ]
);
