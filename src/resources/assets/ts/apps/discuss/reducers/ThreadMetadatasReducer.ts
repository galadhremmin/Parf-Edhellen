import collectivize from '@root/utilities/redux/collectivize';
import { Actions } from '../actions';
import { keyGenerator } from './key-generator';
import ThreadMetadataReducer from './ThreadMetadataReducer';

export default collectivize(
    ThreadMetadataReducer,
    action => keyGenerator(action.entityType, action.entityId),
    [
        Actions.ReceiveThreadMetadata,
        Actions.RequestThreadMetadata,
    ]
);
