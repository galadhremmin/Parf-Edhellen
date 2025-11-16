import  { type ISentenceFragmentEntity, SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import { mapArray } from '@root/utilities/func/mapper';

import { Actions } from '../actions';
import type {
    FragmentsReducerState,
    IFragmentsReducerState,
} from './FragmentsReducer._types';
import type { ISentenceReducerAction } from './SentenceReducer._types';

const findNextFragmentId = (fragment: ISentenceFragmentEntity, fragments: ISentenceFragmentEntity[],
    index: number, direction: number) => {
    if (fragment.lexicalEntryId === null ||
        index < 0 ||
        index >= fragments.length) {
        return null;
    }

    for (let i = index + direction; i > -1 && i < fragments.length; i += direction) {
        if (fragments[i].lexicalEntryId) {
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
                lexicalEntryId: 'lexicalEntryId',
                id: (v) => v.type === SentenceFragmentType.Word ? (v.id || 0) : 0,
                lexicalEntryInflections: (v: ISentenceFragmentEntity) => v.lexicalEntryInflections?.map((i) => ({
                    ...i,
                    inflection: action.sentence.inflections[i.inflectionId.toString(10)] || null,
                })) || [],
                nextFragmentId: (v, i) => findNextFragmentId(v, fragments, i, 1),
                previousFragmentId: (v, i) => findNextFragmentId(v, fragments, i, -1),
                sentenceNumber: 'sentenceNumber',
                speech: (v) => v.speechId ? action.sentence.speeches[v.speechId.toString(10)]?.name : null,
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
