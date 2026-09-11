import {
    describe,
    expect,
    test,
} from '@jest/globals';

import type { ILexicalEntryInflection, ISentenceResponse } from '@root/connectors/backend/IBookApi';
import { SentenceFragmentType } from '@root/connectors/backend/IBookApi';

import Actions from '../actions/Actions';
import FragmentsReducer, { isChangedForm } from './FragmentsReducer';

const sentence = (fragments: unknown[]): ISentenceResponse => ({
    inflections: {},
    sentence: { id: 1 },
    sentenceFragments: fragments,
    sentenceTransformations: {},
    sentenceTranslations: [],
    speeches: {},
}) as unknown as ISentenceResponse;

const fragment = (id: number, form: string, headword: string, comments = '') => ({
    comments,
    fragment: form,
    id,
    lexicalEntry: { id: id * 100, word: { id: 1, word: headword } },
    lexicalEntryId: id * 100,
    lexicalEntryInflections: [] as ILexicalEntryInflection[],
    type: SentenceFragmentType.Word,
});

describe('apps/sentence-inspector/reducers/FragmentsReducer', () => {
    describe('isChangedForm', () => {
        test('treats a trailing hyphen as the lemma convention it is', () => {
            // `penna` in the text is the same word as the entry `penna-`; the hyphen only
            // marks a verb stem.
            expect(isChangedForm('penna', 'penna-')).toEqual(false);
            expect(isChangedForm('linnathon', 'linna-')).toEqual(true);
        });

        test('ignores case, since a line may begin with a capital', () => {
            expect(isChangedForm('Elbereth', 'elbereth')).toEqual(false);
        });

        test('reports a genuinely different written form', () => {
            expect(isChangedForm('chaered', 'haered')).toEqual(true);
            expect(isChangedForm('aear', 'gaear')).toEqual(true);
        });

        test('claims nothing when either form is missing', () => {
            expect(isChangedForm('chaered', null)).toEqual(false);
            expect(isChangedForm(null, 'haered')).toEqual(false);
        });
    });

    test('derives the headword, the gloss and the changed form from the linked entry', () => {
        const state = FragmentsReducer([], {
            sentence: sentence([ fragment(1, 'chaered', 'haered') ]),
            type: Actions.ReceiveSentence,
        });

        expect(state[0].headword).toEqual('haered');
        expect(state[0].isChanged).toEqual(true);
    });

    test('marks the words an editor has written about', () => {
        const state = FragmentsReducer([], {
            sentence: sentence([
                fragment(1, 'beth', 'peth', 'Mutated (as object immediately after verb).'),
                fragment(2, 'lammen', 'lammen'),
                fragment(3, 'hi', 'hi', '   '),
            ]),
            type: Actions.ReceiveSentence,
        });

        expect(state.map((f) => f.hasNote)).toEqual([ true, false, false ]);
    });
});
