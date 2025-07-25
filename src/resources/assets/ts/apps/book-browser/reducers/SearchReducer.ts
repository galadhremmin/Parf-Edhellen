import { Actions } from '../actions';
import {
    ISearchReduxAction,
    ISearchState,
} from './SearchReducer._types';

const SearchReducer = (state: ISearchState = {
    lexicalEntryGroupIds: [],
    includeOld: true,
    itemIndex: -1,
    languageId: 0,
    loading: false,
    reversed: false,
    speechIds: [],
    word: '',
}, action: ISearchReduxAction) => {
    switch (action.type) {
        case Actions.RequestSearchResults:
            return {
                ...state,
                itemIndex: -1,
                loading: true,

                lexicalEntryGroupIds: action.lexicalEntryGroupIds || [],
                includeOld: action.includeOld,
                languageId: action.languageId,
                reversed: action.reversed,
                speechIds: action.speechIds || [],
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
