import {
    Actions,
} from './constants';
import { IGlossaryAction } from './GlossaryReducer._types';
import { ILanguagesState } from './LanguagesReducer._types';

const LanguagesReducer = (state: ILanguagesState = {
    common: [],
    isEmpty: true,
    unusual: [],
}, action: IGlossaryAction) => {
    switch (action.type) {
        case Actions.ReceiveGlossary:
            return {
                common: action.glossary.sections //
                    .filter((section) => !section.language.isUnusual) //
                    .map((section) => section.language),
                isEmpty: action.glossary.sections.length === 0,
                unusual: action.glossary.sections //
                    .filter((section) => section.language.isUnusual) //
                    .map((section) => section.language),
            };
        default:
            return state;
    }
};

export default LanguagesReducer;
