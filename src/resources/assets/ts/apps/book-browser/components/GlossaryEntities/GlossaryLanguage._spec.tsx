import {
    describe,
    expect,
    test,
} from '@jest/globals';

import type { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import { findFeaturedEntry } from './GlossaryLanguage';

function entry(overrides: Partial<ILexicalEntryEntity>): ILexicalEntryEntity {
    return {
        id: 1,
        isLatest: true,
        isOld: false,
        isRejected: false,
        word: 'galadh',
        ...overrides,
    } as unknown as ILexicalEntryEntity;
}

describe('apps/book-browser/components/GlossaryEntities/GlossaryLanguage', () => {
    test('returns null when there are fewer than two entries', () => {
        expect(findFeaturedEntry([])).toBeNull();
        expect(findFeaturedEntry([entry({ id: 1 })])).toBeNull();
    });

    test('returns the first entry when it qualifies', () => {
        const entries = [entry({ id: 1 }), entry({ id: 2 })];
        expect(findFeaturedEntry(entries)).toBe(entries[0]);
    });

    test('skips outdated, rejected and superseded entries', () => {
        const qualifying = entry({ id: 4 });
        const entries = [
            entry({ id: 1, isOld: true }),
            entry({ id: 2, isRejected: true }),
            entry({ id: 3, isLatest: false }),
            qualifying,
        ];
        expect(findFeaturedEntry(entries)).toBe(qualifying);
    });

    test('returns null when no entry qualifies', () => {
        const entries = [
            entry({ id: 1, isOld: true }),
            entry({ id: 2, isRejected: true }),
        ];
        expect(findFeaturedEntry(entries)).toBeNull();
    });
});
