import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import { keyGenerator } from './key-generator';
import NewPostReducer from './NewPostReducer';

export default collectivize(
    NewPostReducer,
    action => keyGenerator(action.entityType, action.entityId),
    [
        Actions.ReceiveCreatePost,
        Actions.RequestCreatePost,
        Actions.ChangeNewPost,
        Actions.CreateNewPost,
        Actions.DiscardNewPost,
    ]
);
