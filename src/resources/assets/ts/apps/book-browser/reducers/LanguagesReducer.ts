import {
    Actions,
} from './constants';
import { IGlossaryAction } from './GlossaryReducer._types';
import { LanguagesState } from './LanguagesReducer._types';

const LanguagesReducer = (state: LanguagesState = [], action: IGlossaryAction) => {
    switch (action.type) {
        case Actions.ReceiveGlossary:
            return action.glossary.sections.map(
                (section) => section.language,
            );
        default:
            return state;
    }
};

export default LanguagesReducer;
