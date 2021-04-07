import { Actions } from '../actions';
import { IEntitiesAction } from './EntitiesReducer._types';
import { ISectionsState } from './SectionsReducer._types';

const SectionsReducer = (state: ISectionsState = {}, action: IEntitiesAction) => {
    switch (action.type) {
        case Actions.ReceiveEntities:
            return action.entities.sections.reduce<ISectionsState>((map, section) => {
                map[section.language.id] = section.entities;
                return map;
            }, {});
        default:
            return state;
    }
};

export default SectionsReducer;
