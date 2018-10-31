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
import { LanguagesState } from '../reducers/LanguagesReducer._types';
import { GlossaryContainer } from './GlossaryContainer';

import '../../../utilities/Enzyme';
import { Actions } from '../reducers/constants';
import LanguagesReducer from '../reducers/LanguagesReducer';

// Define node `require` for synchronous file loading
declare var require: any;

describe('apps/book-browser/components/GlossaryContainer', () => {
    let wrapper: ReactWrapper;

    let glossary: IGlossaryState;
    let glosses: IGlossesState;
    let languages: LanguagesState;

    before(() => {
        const testData = snakeCasePropsToCamelCase<IGlossaryResponse>(
            require('./GlossaryContainer._spec.glossary'),
        );
        const action = {
            glossary: testData,
            type: Actions.ReceiveGlossary,
        };
        glossary = GlossaryReducer(null, action);
        glosses = GlossesReducer(null, action);
        languages = LanguagesReducer(null, action);

        wrapper = mount(<GlossaryContainer
            glosses={glosses}
            languages={languages}
            loading={false}
            single={false}
            word={glossary.word}
        />);
    });

    it('is loading', () => {
        wrapper.setProps({ loading: true });

        // TODO
    });
});
