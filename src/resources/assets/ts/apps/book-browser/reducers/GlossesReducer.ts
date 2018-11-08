import Actions from './Actions';
import { IGlossaryAction } from './GlossaryReducer._types';
import { IGlossesState } from './GlossesReducer._types';

const GlossesReducer = (state: IGlossesState = {}, action: IGlossaryAction) => {
    switch (action.type) {
        case Actions.ReceiveGlossary:
            return action.glossary.sections.reduce<IGlossesState>((map, section) => {
                map[section.language.id] = section.glosses;
                return map;
            }, {});
        default:
            return state;
    }
};

export default GlossesReducer;
