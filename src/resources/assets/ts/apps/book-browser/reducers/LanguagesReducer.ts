import {
    Actions,
} from './constants';
import {
    ILanguagesAction,
    LanguagesState,
} from './LanguagesReducer._types';

const LanguagesReducer = (state: LanguagesState = [], action: ILanguagesAction) => {
    switch (action.type) {
        case Actions.RequestGlossary:
            return state;
        case Actions.ReceiveGlossary:
            return state;
    }

    return state;
};

export default LanguagesReducer;
