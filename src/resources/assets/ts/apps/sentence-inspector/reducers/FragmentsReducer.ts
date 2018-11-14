import { ISentenceFragmentEntity } from '@root/connectors/backend/BookApiConnector._types';
import { mapArray } from '@root/utilities/func/mapper';

import Actions from './Actions';
import {
    FragmentsReducerState,
    IFragmentsReducerState,
} from './FragmentsReducer._types';
import { ISentenceReducerAction } from './SentenceReducer._types';

const FragmentsReducer = (state: FragmentsReducerState = [], action: ISentenceReducerAction) => {
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
            }, action.sentence.sentenceFragments);
        default:
            return state;
    }
};

export default FragmentsReducer;
