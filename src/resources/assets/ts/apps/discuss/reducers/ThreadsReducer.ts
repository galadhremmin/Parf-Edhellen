import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import { keyGenerator } from './key-generator';
import ThreadReducer from './ThreadReducer';

export default collectivize(
    ThreadReducer,
    action => keyGenerator(action.entityType, action.entityId),
    [
        Actions.ReceiveThread,
        Actions.RequestThread,
    ]
);
