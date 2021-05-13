import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';

import { IEntitiesResponse } from '@root/connectors/backend/IBookApi';
import { snakeCasePropsToCamelCase } from '@root/utilities/func/snake-case';
import { Actions } from '../../actions';
import {
    IEntitiesState,
} from '../../reducers/EntitiesReducer._types';
import EntitiesReducer from '../../reducers/EntitiesReducer';
import SectionsReducer from '../../reducers/SectionsReducer';
import { ISectionsState } from '../../reducers/SectionsReducer._types';
import { ILanguagesState } from '../../reducers/LanguagesReducer._types';
import LanguagesReducer from '../../reducers/LanguagesReducer';
import GlossaryEntities from './GlossaryEntities';

import '@root/utilities/Enzyme';

// Define node `require` for synchronous file loading
declare var require: any;

describe('apps/book-browser/containers/GlossaryEntities', () => {
    let wrapper: ReactWrapper;

    let glossary: IEntitiesState;
    let sections: ISectionsState;
    let languages: ILanguagesState;

    before(() => {
        const testData = snakeCasePropsToCamelCase<IEntitiesResponse<any>>(
            require('./GlossaryEntities._spec.glossary'),
        );
        const action: any = {
            ...testData,
            type: Actions.ReceiveEntities,
        };
        glossary = EntitiesReducer(null, action);
        sections = SectionsReducer(null, action);
        languages = LanguagesReducer(null, action);

        wrapper = mount(<GlossaryEntities
            sections={sections}
            isEmpty={false}
            languages={languages.common}
            loading={false}
            single={false}
            unusualLanguages={languages.unusual}
            word={glossary.word}
        />);
    });

    it('is loading', () => {
        wrapper.setProps({ loading: true });

        expect(wrapper.find('Spinner')).to.exist;
    });

    it('displays results', () => {
        wrapper.setProps({ loading: false });
        wrapper.setState({ showUnusualLanguages: true });

        // the test data collection contains unusual and common languages, so there
        // should be two sections, one of which shouldb be flagged as `unusual`.
        expect(wrapper.find('.ed-glossary').length).to.equal(2);

        const unusualSection = wrapper.find('.ed-glossary.ed-glossary--unusual');
        expect(unusualSection).to.exist;
        expect(unusualSection.text()).to.contain('There are more words but they are from Tolkien\'s earlier conceptional periods');

        // Expect there to be a `Language` component per language.
        expect(wrapper.find('.ed-glossary__language').length).to.equal(
            languages.unusual.length + languages.common.length,
        );
    });

    it('should not render because word is empty', () => {
        wrapper.setProps({ isEmpty: true });

        expect(wrapper.isEmptyRender()).to.be.false;
        expect(wrapper.text()).to.contain('Alas! What you are looking for does not exist!');
    });
});
