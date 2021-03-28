import { SearchResultGlossaryGroupId } from '@root/config';
import { Actions } from '../actions';
import { IEntitiesAction } from './EntitiesReducer._types';
import { IGlossesState } from './GlossesReducer._types';

const GlossesReducer = (state: IGlossesState = {}, action: IEntitiesAction) => {
    // This reducer only supports the glossary.
    if (action.groupId !== SearchResultGlossaryGroupId) {
        return state;
    }

    switch (action.type) {
        case Actions.ReceiveEntities:
            return action.entities.sections.reduce<IGlossesState>((map, section) => {
                map[section.language.id] = section.glosses;
                return map;
            }, {});
        default:
            return state;
    }
};

export default GlossesReducer;
