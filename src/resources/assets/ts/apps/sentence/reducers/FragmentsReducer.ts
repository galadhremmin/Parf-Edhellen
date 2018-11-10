import { ISentenceFragmentEntity } from '@root/connectors/backend/BookApiConnector._types';
import { mapArray } from '@root/utilities/func/mapper';

import Actions from './Actions';
import {
    FragmentsReducerState,
    IFragmentsReducerAction,
    IFragmentsReducerState,
} from './FragmentsReducer._types';

const FragmentsReducer = (state: FragmentsReducerState = [], action: IFragmentsReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return mapArray<ISentenceFragmentEntity, IFragmentsReducerState>({
                comments: 'comments',
                glossId: 'glossId',
                id: 'id',
                sentenceNumber: 'sentenceNumber',
                speech: 'speech',
                speechId: 'speechId',
                type: 'type',
            }, action.sentence.fragments);
        default:
            return state;
    }
};

export default FragmentsReducer;
