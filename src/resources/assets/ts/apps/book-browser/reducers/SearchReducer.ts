import {
    ISearchAction,
    ISearchState,
} from '../actions/SearchActions.types';
import {
    Actions,
} from './constants';

const SearchReducer = (state: ISearchState = {
    includeOld: true,
    itemIndex: -1,
    languageId: 0,
    loading: false,
    reversed: false,
    word: '',
}, action: ISearchAction) => {
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
    }

    return state;
};

export default SearchReducer;
