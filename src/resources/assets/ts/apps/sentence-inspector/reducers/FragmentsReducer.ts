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

/**
 * A trailing hyphen is how the dictionary writes a verb stem -- `penna-`, `linna-` -- and
 * not a difference in the word. Everything else is: `chaered` really is written otherwise
 * than `haered`, which is the thing worth showing.
 */
const normaliseForm = (word: string) => (word || '').toLocaleLowerCase().replace(/-+$/, '');

export const isChangedForm = (fragment: string, headword: string) =>
    Boolean(fragment) && Boolean(headword) &&
    normaliseForm(fragment) !== normaliseForm(headword);

const FragmentsReducer = (state: FragmentsReducerState = [], action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence: {
            const fragments = action.sentence.sentenceFragments;
            const { wordIndices, positionOf } = indexWordFragments(fragments);
            return mapArray<ISentenceFragmentEntity, IFragmentsReducerState>({
                collection: (v) => v.lexicalEntry?.lexicalEntryGroup?.name || null,
                comments: 'comments',
                fragment: 'fragment',
                gloss: (v) => v.lexicalEntry?.sense?.word?.word || null,
                hasNote: (v) => Boolean(v.comments && v.comments.trim() !== ''),
                headword: (v) => v.lexicalEntry?.word?.word || null,
                isChanged: (v) => isChangedForm(v.fragment, v.lexicalEntry?.word?.word),
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
                paragraphNumber: 'paragraphNumber',
                previousFragmentId: (_v, i) => {
                    const pos = positionOf.get(i);
                    return pos !== undefined && pos > 0
                        ? (fragments[wordIndices[pos - 1]].id ?? null) : null;
                },
                sentenceNumber: 'sentenceNumber',
                source: (v) => v.lexicalEntry?.source || null,
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
