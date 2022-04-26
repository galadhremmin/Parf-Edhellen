import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import NewPostReducer from './NewPostReducer';

export default collectivize(
    NewPostReducer,
    (e, a) => e.forumThreadId === a.forumThreadId,
    [
        Actions.ReceiveCreatePost,
        Actions.RequestCreatePost,
        Actions.ChangeNewPost,
        Actions.CreateNewPost,
        Actions.DiscardNewPost,
    ]
);
