import  { type ISentenceFragmentEntity, SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import { mapArray } from '@root/utilities/func/mapper';

import { Actions } from '../actions';
import type {
    FragmentsReducerState,
    IFragmentsReducerState,
} from './FragmentsReducer._types';
import type { ISentenceReducerAction } from './SentenceReducer._types';

const indexWordFragments = (fragments: ISentenceFragmentEntity[]) => {
    const wordIndices: number[] = [];
    for (let i = 0; i < fragments.length; i++) {
        if (fragments[i].lexicalEntryId) {
            wordIndices.push(i);
        }
    }
    // Map each fragment array index → its position within wordIndices
    const positionOf = new Map<number, number>(wordIndices.map((idx, pos) => [idx, pos]));
    return { wordIndices, positionOf };
};

const FragmentsReducer = (state: FragmentsReducerState = [], action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence: {
            const fragments = action.sentence.sentenceFragments;
            const { wordIndices, positionOf } = indexWordFragments(fragments);
            return mapArray<ISentenceFragmentEntity, IFragmentsReducerState>({
                comments: 'comments',
                fragment: 'fragment',
                lexicalEntryId: 'lexicalEntryId',
                id: (v) => v.type === SentenceFragmentType.Word ? (v.id || 0) : 0,
                lexicalEntryInflections: (v: ISentenceFragmentEntity) => v.lexicalEntryInflections?.map((i) => ({
                    ...i,
                    inflection: action.sentence.inflections[i.inflectionId.toString(10)] || null,
                })) || [],
                nextFragmentId: (_v, i) => {
                    const pos = positionOf.get(i);
                    return pos !== undefined && pos + 1 < wordIndices.length
                        ? (fragments[wordIndices[pos + 1]].id ?? null) : null;
                },
                previousFragmentId: (_v, i) => {
                    const pos = positionOf.get(i);
                    return pos !== undefined && pos > 0
                        ? (fragments[wordIndices[pos - 1]].id ?? null) : null;
                },
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
