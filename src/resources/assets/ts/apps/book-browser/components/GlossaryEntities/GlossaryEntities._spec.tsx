import {
    beforeAll,
    describe,
    expect,
    test,
} from '@jest/globals';
import { render, screen, waitFor } from '@testing-library/react';
import * as sinon from 'sinon';

import type { ILexicalEntryEntity, IEntitiesResponse } from '@root/connectors/backend/IBookApi';
import setupContainer from '@root/di/config';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';
import { Actions } from '../../actions';
import LanguagesReducer from '../../reducers/CategoriesReducer';
import EntitiesReducer from '../../reducers/EntitiesReducer';
import SectionsReducer from '../../reducers/SectionsReducer';
import GlossaryEntities from './GlossaryEntities';

// Define node `require` for synchronous file loading
declare var require: any;

// jsdom does not implement IntersectionObserver — stub it so
// GlossaryMinimap (and anything else that uses it) can mount.
beforeAll(() => {
    (global as any).IntersectionObserver = class {
        observe() {}
        unobserve() {}
        disconnect() {}
    };
});

describe('apps/book-browser/containers/GlossaryEntities', () => {
    beforeAll(() => {
        setupContainer();
    });

    test('displays results', async () => {
        const testData = snakeCasePropsToCamelCase<IEntitiesResponse<any>>(
            require('./GlossaryEntities._spec.glossary'),
        );
        const action: any = {
            ...testData,
            type: Actions.ReceiveEntities,
        };
        const glossary = EntitiesReducer(null, action);
        const sections = SectionsReducer(null, action);
        const languages = LanguagesReducer(null, action);

        const { container } = render(<GlossaryEntities
            sections={sections}
            isEmpty={false}
            languages={languages.common}
            loading={false}
            single={false}
            unusualLanguages={languages.unusual}
            forceShowUnusualLanguages={true}
            word={glossary.word}
        />);

        await waitFor(() => {
            const languageTitles = screen.getAllByRole('heading', {
                level: 2,
            });
            const expectedLanguages = languages.common.concat(languages.unusual).map((language) => language.name);

            expect(languageTitles).toHaveLength(expectedLanguages.length);
            expect(languageTitles.map((header) => header.querySelector('.language-name').textContent)).toEqual(expectedLanguages);

            const wordBlocks = screen.getAllByRole('heading', {
                level: 3,
            });
            const expectedWords = Object.values(sections).flat(1) as ILexicalEntryEntity[];
            expect(wordBlocks).toHaveLength(expectedWords.length + 1 /* because of "There are more words but they are from Tolkien's earlier conceptional periods" */);
            expect(wordBlocks.map(block => block.textContent)).toContain('The entries below are from Tolkien\'s earlier conceptional periods');

            // Each language section features its best match full-width at the top.
            const featuredCards = container.querySelectorAll('.ed-glossary__language__featured .lexical-entry--featured');
            const sectionsWithFeaturedEntry = Object.values(sections).filter((entries: ILexicalEntryEntity[]) =>
                entries.length >= 2 && entries.some((e) => e.isLatest && ! e.isRejected && ! e.isOld));
            expect(featuredCards).toHaveLength(sectionsWithFeaturedEntry.length);
            const firstSectionTopEntry = sections[languages.common[0].id][0];
            expect(featuredCards[0].querySelector('[itemprop="headline"]').textContent).toEqual(firstSectionTopEntry.word);

            // Outdated (is_old) entries are demoted and grouped after current entries.
            const demotedCards = container.querySelectorAll('.ed-glossary__language__words--outdated .lexical-entry--demoted');
            const expectedOldWords = expectedWords.filter((entry) => !! entry.isOld);
            expect(demotedCards).toHaveLength(expectedOldWords.length);
            expect(container.textContent).toContain('Older sources');
        });
    });

    test('dispatches the real Redux dispatch (not the SearchActions instance) on popstate', async () => {
        const dispatch = sinon.spy();

        render(<GlossaryEntities
            dispatch={dispatch}
            sections={{}}
            isEmpty={true}
            loading={false}
            single={false}
            word=""
        />);

        window.dispatchEvent(new PopStateEvent('popstate', {
            state: {
                glossary: true,
                groupId: 1,
                word: 'galadh',
            },
        }));

        await waitFor(() => {
            expect(dispatch.calledOnce).toBe(true);
            expect(typeof dispatch.firstCall.args[0]).toBe('function');
        });
    });
});
