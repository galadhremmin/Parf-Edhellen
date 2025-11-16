import { Actions } from '../actions';
import type { IEntitiesAction } from './EntitiesReducer._types';
import type { ICategoriesState } from './CategoriesReducer._types';

const LanguagesReducer = (state: ICategoriesState = {
    common: [],
    isEmpty: true,
    unusual: [],
}, action: IEntitiesAction) => {
    switch (action.type) {
        case Actions.ReceiveEntities:
            return {
                common: action.entities.sections //
                    .filter((section) => !section.language.isUnusual) //
                    .map((section) => section.language),
                isEmpty: action.entities.sections.length === 0,
                unusual: action.entities.sections //
                    .filter((section) => section.language.isUnusual) //
                    .map((section) => section.language),
            };
        default:
            return state;
    }
};

export default LanguagesReducer;
