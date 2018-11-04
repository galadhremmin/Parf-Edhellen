import { expect } from 'chai';
import {
    mount,
    ReactWrapper,
} from 'enzyme';
import React from 'react';

import { IGlossaryResponse, IGlossEntity } from '../../../connectors/backend/BookApiConnector._types';
import { snakeCasePropsToCamelCase } from '../../../utilities/func/snake-case';
import GlossaryReducer from '../reducers/GlossaryReducer';
import { IGlossaryState } from '../reducers/GlossaryReducer._types';
import GlossesReducer from '../reducers/GlossesReducer';
import { IGlossesState } from '../reducers/GlossesReducer._types';
import { ILanguagesState } from '../reducers/LanguagesReducer._types';
import { Glossary } from './Glossary';

import '../../../utilities/Enzyme';
import { Actions } from '../reducers/constants';
import LanguagesReducer from '../reducers/LanguagesReducer';

// Define node `require` for synchronous file loading
declare var require: any;

describe('apps/book-browser/containers/Glossary', () => {
    let wrapper: ReactWrapper;

    let glossary: IGlossaryState;
    let glosses: IGlossesState;
    let languages: ILanguagesState;

    before(() => {
        const testData = snakeCasePropsToCamelCase<IGlossaryResponse>(
            require('./Glossary._spec.glossary'),
        );
        const action = {
            glossary: testData,
            type: Actions.ReceiveGlossary,
        };
        glossary = GlossaryReducer(null, action);
        glosses = GlossesReducer(null, action);
        languages = LanguagesReducer(null, action);

        wrapper = mount(<Glossary
            glosses={glosses}
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

        // the test data collection contains unusual and common languages, so there
        // should be two sections, one of which shouldb be flagged as `unusual`.
        expect(wrapper.find('.ed-glossary').length).to.equal(2);

        const unusualSection = wrapper.find('.ed-glossary.ed-glossary--unusual');
        expect(unusualSection).to.exist;
        expect(unusualSection.text()).to.contain('Beware, older languages below!');

        // Expect there to be a `Language` component per language.
        expect(wrapper.find('.ed-glossary__language').length).to.equal(
            languages.unusual.length + languages.common.length,
        );
    });

    it('should not render because word is empty', () => {
        wrapper.setProps({ word: '' });

        expect(wrapper.isEmptyRender()).to.be.true;
    });
});
