import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import ThreadReducer from './ThreadReducer';

export default collectivize(
    ThreadReducer,
    (e, a) => {
        if (a.entityId !== undefined && e.entityType !== undefined) {
            return e.entityId === a.entityId && e.entityType === a.entityType;
        } else {
            return e.id === (a.threadData.threadId ?? a.threadData.thread.id);
        }
    },
    [
        Actions.ReceiveThread,
        Actions.RequestThread,
    ]
);
