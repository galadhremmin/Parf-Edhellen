import { SearchResultGlossaryGroupId } from '@root/config';
import { Actions } from '../actions';
import { IEntitiesAction } from './EntitiesReducer._types';
import { ILanguagesState } from './LanguagesReducer._types';

const LanguagesReducer = (state: ILanguagesState = {
    common: [],
    isEmpty: true,
    unusual: [],
}, action: IEntitiesAction) => {
    // This reducer only supports the glossary.
    if (action.groupId !== SearchResultGlossaryGroupId) {
        return state;
    }

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
