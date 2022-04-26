import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import ThreadMetadataReducer from './ThreadMetadataReducer';

export default collectivize(
    ThreadMetadataReducer,
    (e, a) => e.forumThreadId === a.forumThreadId,
    [
        Actions.ReceiveThreadMetadata,
        Actions.RequestThreadMetadata,
    ]
);
