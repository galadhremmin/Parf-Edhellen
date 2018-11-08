import Actions from './Actions';
import {
    ISearchReduxAction,
    ISearchState,
} from './SearchReducer._types';

const SearchReducer = (state: ISearchState = {
    includeOld: true,
    itemIndex: -1,
    languageId: 0,
    loading: false,
    reversed: false,
    word: '',
}, action: ISearchReduxAction) => {
    switch (action.type) {
        case Actions.RequestSearchResults:
            return {
                ...state,
                itemIndex: -1,
                loading: true,

                includeOld: action.includeOld,
                languageId: action.languageId,
                reversed: action.reversed,
                word: action.word,
            };
        case Actions.ReceiveSearchResults:
            return {
                ...state,
                loading: false,
            };
        default:
            return state;
    }
};

export default SearchReducer;
