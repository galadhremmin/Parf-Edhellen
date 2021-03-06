import { ISentenceFragmentEntity, SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import { mapArray } from '@root/utilities/func/mapper';

import { Actions } from '../actions';
import {
    FragmentsReducerState,
    IFragmentsReducerState,
} from './FragmentsReducer._types';
import { ISentenceReducerAction } from './SentenceReducer._types';

const findNextFragmentId = (fragment: ISentenceFragmentEntity, fragments: ISentenceFragmentEntity[],
    index: number, direction: number) => {
    if (fragment.glossId === null ||
        index < 0 ||
        index >= fragments.length) {
        return null;
    }

    for (let i = index + direction; i > -1 && i < fragments.length; i += direction) {
        if (fragments[i].glossId) {
            return fragments[i].id;
        }
    }

    return null;
};

const FragmentsReducer = (state: FragmentsReducerState = [], action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence: {
            const fragments = action.sentence.sentenceFragments;
            return mapArray<ISentenceFragmentEntity, IFragmentsReducerState>({
                comments: 'comments',
                fragment: 'fragment',
                glossId: 'glossId',
                id: (v) => v.type === SentenceFragmentType.Word ? (v.id || 0) : 0,
                inflections: (v) => {
                    if (Array.isArray(v.inflections)) {
                        return v.inflections.map<any>((i) => action.sentence.inflections[i.inflectionId]);
                    }

                    return action.sentence.inflections[v.id.toString(10)]
                },
                nextFragmentId: (v, i) => findNextFragmentId(v, fragments, i, 1),
                previousFragmentId: (v, i) => findNextFragmentId(v, fragments, i, -1),
                sentenceNumber: 'sentenceNumber',
                speech: (v) => action.sentence.speeches[v.speechId],
                speechId: 'speechId',
                tengwar: 'tengwar',
                type: 'type',
            }, fragments);
        }
        default:
            return state;
    }
};

export default FragmentsReducer;
