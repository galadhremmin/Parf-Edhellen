import { Actions } from '../actions';
import type { IEntitiesAction } from './EntitiesReducer._types';
import type { ISectionsState } from './SectionsReducer._types';

const SectionsReducer = (state: ISectionsState = {}, action: IEntitiesAction): ISectionsState => {
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
