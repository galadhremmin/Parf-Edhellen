import {
    ISearchAction,
    ISearchState,
} from '../_types';
import {
    Actions,
} from './constants';

const SearchReducer = (state: ISearchState = {
    includeOld: true,
    itemIndex: -1,
    languageId: 0,
    loading: false,
    query: '',
    reversed: false,
}, action: ISearchAction) => {
    switch (action.type) {
        case Actions.RequestSearchResults:
            return {
                ...state,
                itemIndex: -1,
                loading: true,

                includeOld: action.includeOld,
                languageId: action.languageId,
                query: action.query,
                reversed: action.reversed,
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
