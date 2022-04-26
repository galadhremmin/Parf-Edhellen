import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import { keyGenerator } from './key-generator';
import PostPaginationReducer from './PostPaginationReducer';

export default collectivize(
    PostPaginationReducer,
    action => keyGenerator(action.entityType, action.entityId),
    [
        Actions.ReceiveThread,
    ]
);
