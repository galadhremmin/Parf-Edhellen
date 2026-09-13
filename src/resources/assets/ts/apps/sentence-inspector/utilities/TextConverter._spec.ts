import {
    describe,
    expect,
    test,
} from '@jest/globals';

import type {
    ISentenceFragmentEntity,
    ISentenceTranslation,
    ITextTransformationsMap,
    ParagraphTransformation,
} from '@root/connectors/backend/IBookApi';
import { SentenceFragmentType } from '@root/connectors/backend/IBookApi';

import convertTransformationsToLines, { PROSE_AT } from './TextConverter';

const word = (id: number, fragment: string, tengwar: string): ISentenceFragmentEntity => ({
    fragment,
    id,
    lexicalEntryId: id * 100,
    paragraphNumber: 1,
    sentenceNumber: 1,
    tengwar,
    type: SentenceFragmentType.Word,
});

const punctuation = (id: number, fragment: string): ISentenceFragmentEntity => ({
    fragment,
    id,
    paragraphNumber: 1,
    sentenceNumber: 1,
    type: SentenceFragmentType.Interpunctuation,
});

describe('apps/sentence-inspector/utilities/TextConverter', () => {
    // A, Elbereth, Gilthoniel -- the opening of the hymn, with its exclamation mark.
    const Fragments: ISentenceFragmentEntity[] = [
        word(1, 'A', 'x'),
        word(2, 'Elbereth', 'y'),
        word(3, 'Gilthoniel', 'z'),
        punctuation(4, '!'),
    ];

    const Transformations: ITextTransformationsMap = {
        latin: { 1: [[0], ' ', [1], ' ', [2], [3]] },
        tengwar: { 1: [[0, 'x'], ' ', [1, 'y'], ' ', [2, 'z'], [3, '!']] },
    };

    const Translations: ISentenceTranslation[] = [
        { paragraphNumber: 1, sentenceNumber: 1, translation: 'O Elbereth Starkindler,' },
    ];

    test('builds one line per paragraph, joining words to the fragments they stand for', () => {
        const state = convertTransformationsToLines(Transformations, Translations, Fragments);

        expect(state.lines.length).toEqual(1);
        expect(state.shape).toEqual('verse');
        expect(state.lines[0].kind).toEqual('stanza');
        expect(state.lines[0].n).toEqual(1);

        expect(state.lines[0].latin.map((t) => t.text)).toEqual([ 'A', ' ', 'Elbereth', ' ', 'Gilthoniel', '!' ]);

        // Punctuation and separators are not words, so they cannot be opened.
        expect(state.lines[0].latin.map((t) => t.fragmentId)).toEqual([ 1, 0, 2, 0, 3, 0 ]);
        expect(state.lines[0].wordCount).toEqual(3);
    });

    test('carries the tengwar of each word, for prose to set beside its transcription', () => {
        const state = convertTransformationsToLines(Transformations, Translations, Fragments);

        expect(state.lines[0].tengwarByFragment).toEqual({ 1: 'x', 2: 'y', 3: 'z' });
    });

    test('joins the translation by paragraph number rather than by position', () => {
        const transformations: ITextTransformationsMap = {
            latin: { 1: [[0]], 2: [[1]], 3: [[2]] },
            tengwar: {},
        };
        // Only the last paragraph is translated. Matching by position would have put this
        // translation under the first line.
        const translations: ISentenceTranslation[] = [
            { paragraphNumber: 3, sentenceNumber: 1, translation: 'the glory of the starry host!' },
        ];

        const state = convertTransformationsToLines(transformations, translations, Fragments);

        expect(state.lines.length).toEqual(3);
        expect(state.lines[0].translation).toBeNull();
        expect(state.lines[1].translation).toBeNull();
        expect(state.lines[2].translation).toEqual('the glory of the starry host!');
        expect(state.hasTranslations).toEqual(true);
    });

    test('reports a phrase with no translations at all', () => {
        const state = convertTransformationsToLines(Transformations, [], Fragments);

        expect(state.hasTranslations).toEqual(false);
        expect(state.lines[0].translation).toBeNull();
    });

    test('draws a paragraph with no letters in it as a section rule', () => {
        const fragments = [ word(1, 'Túrin', 'x'), punctuation(2, '------------') ];
        const transformations: ITextTransformationsMap = {
            latin: { 1: [[0]], 2: [[1]], 3: [[0]] },
            tengwar: {},
        };

        const state = convertTransformationsToLines(transformations, [], fragments);

        expect(state.lines.map((l) => l.kind)).toEqual([ 'stanza', 'rule', 'stanza' ]);
        // The rule takes no line number: the numbering counts lines of text.
        expect(state.lines.map((l) => l.n)).toEqual([ 1, 0, 2 ]);
    });

    test('sets the whole phrase as prose once any paragraph runs long', () => {
        const fragments: ISentenceFragmentEntity[] = [];
        const paragraph: ParagraphTransformation = [];
        for (let i = 0; i < PROSE_AT + 1; i += 1) {
            fragments.push(word(i + 1, `word${i}`, 'x'));
            paragraph.push([ i ]);
            paragraph.push(' ');
        }

        const state = convertTransformationsToLines(
            { latin: { 1: paragraph, 2: [[0]] }, tengwar: {} },
            [],
            fragments,
        );

        // Never mixed: a document reads as one thing or the other, so the short second
        // paragraph is set as prose alongside the long first one.
        expect(state.shape).toEqual('prose');
        expect(state.lines.map((l) => l.kind)).toEqual([ 'prose', 'prose' ]);
    });

    test('keeps a phrase of short lines as verse', () => {
        const state = convertTransformationsToLines(Transformations, Translations, Fragments);

        expect(state.shape).toEqual('verse');
    });

    test('survives a phrase with no transformations', () => {
        expect(convertTransformationsToLines(null, [], Fragments).lines).toEqual([]);
        expect(convertTransformationsToLines({}, [], Fragments).lines).toEqual([]);
    });
});
