import { describe, expect, test } from '@jest/globals';
import { render } from '@testing-library/react';

import type { IDerivationEntity, IPhoneticDevelopmentEntity } from '@root/connectors/backend/IBookApi';
import LexicalEntryPhoneticDevelopments from './LexicalEntryPhoneticDevelopments';

function makeStep(overrides: Partial<IPhoneticDevelopmentEntity> = {}): IPhoneticDevelopmentEntity {
    return {
        groupUuid: 'group-1',
        order: 0,
        previousWord: null,
        rule: null,
        word: 'galada',
        ...overrides,
    };
}

function makeDerivation(overrides: Partial<IDerivationEntity> = {}): IDerivationEntity {
    return {
        comment: null,
        groupUuid: 'group-1',
        intermediateStages: null,
        isRejected: false,
        isUncertain: false,
        order: 0,
        parentForm: 'galadā',
        parentGloss: null,
        parentLabel: null,
        parentLanguageId: null,
        parentLexicalEntryId: null,
        parentUrl: null,
        parentWord: null,
        source: null,
        ...overrides,
    };
}

describe('apps/book-browser/components/GlossaryEntities/LexicalEntryPhoneticDevelopments', () => {
    test('renders nothing when there are no phonetic developments', () => {
        const { container } = render(<LexicalEntryPhoneticDevelopments derivations={[]} phoneticDevelopments={[]} word="galadh" />);
        expect(container.querySelector('.LexicalEntryPhoneticDevelopments')).toBeFalsy();
    });

    test('composes Development from the derivation\'s parent form and the entry\'s own spelled word, not the phonetic chain\'s raw last stage', () => {
        const { container } = render(<LexicalEntryPhoneticDevelopments
            derivations={[[makeDerivation({ groupUuid: 'group-1', parentForm: 'galadā', source: 'Let/426' })]]}
            phoneticDevelopments={[[
                makeStep({ groupUuid: 'group-1', order: 0, word: 'galadā' }),
                makeStep({ groupUuid: 'group-1', order: 1, word: 'galada', rule: '-Să', previousWord: 'galadā' }),
                makeStep({ groupUuid: 'group-1', order: 2, word: 'galaða', rule: 'Vð', previousWord: 'galada' }),
                // Phonetic chains end on the raw transliterated form ("galað"), not the entry's
                // spelled headword ("galadh") — Development must use the latter.
                makeStep({ groupUuid: 'group-1', order: 3, word: 'galað', rule: '-Sø', previousWord: 'galaða' }),
            ]]}
            word="galadh"
        />);

        expect(container.querySelector('.LexicalEntryPhoneticDevelopments--development')?.textContent)
            .toBe('*galadā > galadh');

        const stages = container.querySelectorAll('.LexicalEntryPhoneticDevelopments--stage');
        expect(Array.from(stages).map((el) => el.textContent)).toEqual([
            '[galadā]', '[galada](-Să)', '[galaða](Vð)', '[galað](-Sø)',
        ]);

        expect(container.querySelector('.LexicalEntryPhoneticDevelopments--source')?.textContent).toContain('Let/426');
    });

    test('includes the derivation\'s own recorded intermediate stages in Development', () => {
        const { container } = render(<LexicalEntryPhoneticDevelopments
            derivations={[[makeDerivation({ parentForm: 'galadā', intermediateStages: ['galaða', 'galað'] })]]}
            phoneticDevelopments={[[makeStep()]]}
            word="galadh"
        />);

        expect(container.querySelector('.LexicalEntryPhoneticDevelopments--development')?.textContent)
            .toBe('*galadā > galaða > galað > galadh');
    });

    test('renders no source when the matching derivation has none', () => {
        const { container } = render(<LexicalEntryPhoneticDevelopments
            derivations={[]}
            phoneticDevelopments={[[makeStep()]]}
            word="galadh"
        />);

        expect(container.querySelector('.LexicalEntryPhoneticDevelopments--source')).toBeFalsy();
    });

    test('renders separate rows for genuinely distinct developments', () => {
        const { container } = render(<LexicalEntryPhoneticDevelopments
            derivations={[]}
            phoneticDevelopments={[
                [makeStep({ groupUuid: 'a' })],
                [makeStep({ groupUuid: 'b', word: 'other' })],
            ]}
            word="galadh"
        />);

        const rows = container.querySelectorAll('.LexicalEntryPhoneticDevelopments--row');
        expect(rows).toHaveLength(2);
        rows.forEach((row) => {
            expect(row.querySelector('.LexicalEntryPhoneticDevelopments--development')?.textContent).toMatch(/> galadh$/);
        });
    });

    test('collapses citations with identical Development and Stages into one row, listing every source', () => {
        // Eldamo records one entry per citation, so the same sound change (galadā > galadh)
        // is frequently attested by several independent sources.
        const identicalChain = () => [
            makeStep({ order: 0, word: 'galadā' }),
            makeStep({ order: 1, word: 'galada', rule: '-Să', previousWord: 'galadā' }),
        ];

        const { container } = render(<LexicalEntryPhoneticDevelopments
            derivations={[
                [makeDerivation({ groupUuid: 'a', source: 'Let/426' })],
                [makeDerivation({ groupUuid: 'b', source: 'PE17/025' })],
                [makeDerivation({ groupUuid: 'c', source: 'PE17/050' })],
            ]}
            phoneticDevelopments={[
                identicalChain().map((s) => ({ ...s, groupUuid: 'a' })),
                identicalChain().map((s) => ({ ...s, groupUuid: 'b' })),
                identicalChain().map((s) => ({ ...s, groupUuid: 'c' })),
            ]}
            word="galadh"
        />);

        const rows = container.querySelectorAll('.LexicalEntryPhoneticDevelopments--row');
        expect(rows).toHaveLength(1);

        const sources = Array.from(rows[0].querySelectorAll('.LexicalEntryPhoneticDevelopments--source'))
            .map((el) => el.textContent);
        expect(sources).toEqual(['✦ Let/426', '✦ PE17/025', '✦ PE17/050']);
    });

    test('keeps a distinct row for a citation with a genuinely different chain (e.g. NM/352\'s shorter derivation)', () => {
        const { container } = render(<LexicalEntryPhoneticDevelopments
            derivations={[
                [makeDerivation({ groupUuid: 'a', source: 'Let/426' })],
                [makeDerivation({ groupUuid: 'b', parentForm: 'galada', source: 'NM/352' })],
            ]}
            phoneticDevelopments={[
                [
                    makeStep({ groupUuid: 'a', order: 0, word: 'galadā' }),
                    makeStep({ groupUuid: 'a', order: 1, word: 'galada', rule: '-Să' }),
                ],
                [
                    makeStep({ groupUuid: 'b', order: 0, word: 'galada' }),
                ],
            ]}
            word="galadh"
        />);

        expect(container.querySelectorAll('.LexicalEntryPhoneticDevelopments--row')).toHaveLength(2);
    });
});
