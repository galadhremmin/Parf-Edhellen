import { expect } from 'chai';
import React from 'react';
import { render, screen } from '@testing-library/react';

import { IEntitiesResponse } from '@root/connectors/backend/IBookApi';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';
import { Actions } from '../../actions';
import EntitiesReducer from '../../reducers/EntitiesReducer';
import SectionsReducer from '../../reducers/SectionsReducer';
import LanguagesReducer from '../../reducers/CategoriesReducer';
import GlossaryEntities from './GlossaryEntities';

// Define node `require` for synchronous file loading
declare var require: any;

describe('apps/book-browser/containers/GlossaryEntities', async () => {
    it('is loading', async () => {
        render(<GlossaryEntities
            sections={[]}
            isEmpty={false}
            languages={[]}
            loading={true}
            single={false}
            unusualLanguages={[]}
            word={null}
        />);

        await screen.findAllByRole('form');
        // expect(container.querySelector('.sk-bounce')).to.exist;
    });

    /*
    it('displays results', () => {
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
            word={glossary.word}
        />);

        // the test data collection contains unusual and common languages, so there
        // should be two sections, one of which shouldb be flagged as `unusual`.
        expect(container.querySelectorAll('.ed-glossary').length).to.equal(2);

        const unusualSection = container.querySelector('.ed-glossary.ed-glossary--unusual');
        expect(unusualSection).to.exist;
        expect(unusualSection.textContent).to.contain('There are more words but they are from Tolkien\'s earlier conceptional periods');

        // Expect there to be a `Language` component per language.
        expect(container.querySelectorAll('.ed-glossary__language').length).to.equal(
            languages.unusual.length + languages.common.length,
        );
    });
    */
});
