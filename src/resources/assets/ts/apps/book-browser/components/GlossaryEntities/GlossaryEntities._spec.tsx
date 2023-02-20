import {
    describe,
    expect,
    test,
} from '@jest/globals';
import { render, screen, waitFor } from '@testing-library/react';

import { IBookGlossEntity, IEntitiesResponse, ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';
import { Actions } from '../../actions';
import EntitiesReducer from '../../reducers/EntitiesReducer';
import SectionsReducer from '../../reducers/SectionsReducer';
import LanguagesReducer from '../../reducers/CategoriesReducer';
import GlossaryEntities from './GlossaryEntities';
import { act } from 'react-dom/test-utils';

// Define node `require` for synchronous file loading
declare var require: any;

describe('apps/book-browser/containers/GlossaryEntities', () => {
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

        render(<GlossaryEntities
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
            const expectedWords = Object.values(sections).flat(1) as IBookGlossEntity[];
            expect(wordBlocks).toHaveLength(expectedWords.length + 1 /* because of "There are more words but they are from Tolkien's earlier conceptional periods" */);
            expect(wordBlocks.map(block => block.textContent)).toContain('There are more words but they are from Tolkien\'s earlier conceptional periods');
        });
    });
});
