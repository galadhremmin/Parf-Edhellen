export const REQUEST_RESULTS    = 'EDSR_REQUEST_RESULTS';
export const REQUEST_NAVIGATION = 'EDSR_REQUEST_NAVIGATION';
export const RECEIVE_RESULTS    = 'EDSR_RECEIVE_RESULTS';
export const RECEIVE_NAVIGATION = 'EDSR_RECEIVE_NAVIGATION';
export const ADVANCE_SELECTION  = 'EDSR_ADVANCE_SELECTION';
export const SET_SELECTION      = 'EDSR_SET_SELECTION';
export const SET_LANGUAGE       = 'EDSR_SET_LANGUAGE';

export const EDSearchResultsReducer = (state = {
    loading: false,
    items: undefined,
    itemIndex: -1,
    word: undefined,
    wordSearch: undefined,
    normalizedWord: undefined,
    bookData: undefined,
    reversed: false,
    includeOld: false,
    languageId: 0
}, action) => {
    switch (action.type) {

        case REQUEST_RESULTS:
            return {
                ...state,
                loading: true,
                wordSearch: action.wordSearch,
                reversed: action.reversed,
                languageId: action.languageId,
                includeOld: action.includeOld,
                itemIndex: -1
            };

        case REQUEST_NAVIGATION:
            // perform an index check -- if the action does not specify
            // an index within the current result set, reset the result set
            // as we can assume that the client has navigated somewhere else
            // (to an entirely different word)
            const index = action.index === undefined ? -1 : action.index;
            const items = index > -1 ? state.items : undefined;

            return {
                ...state,
                loading: true,
                word: action.word,
                normalizedWord: action.normalizedWord,
                itemIndex: index,
                items
            };

        case RECEIVE_RESULTS:
            return {
                ...state,
                items: action.items,
                loading: false,
                itemIndex: -1
            };

        case RECEIVE_NAVIGATION:
            return {
                ...state,
                bookData: action.bookData,
                loading: false
            };

        case ADVANCE_SELECTION:
            if (! Array.isArray(state.items)) {
                return state;
            }

            return {
                ...state,
                itemIndex: action.direction < 0
                    ? (state.itemIndex < 1   ? state.items.length - 1 : state.itemIndex - 1)
                    : (state.itemIndex + 1 === state.items.length ? 0 : state.itemIndex + 1)
            };

        case SET_SELECTION:
            return {
                ...state,
                itemIndex: state.index === -1 || ! Array.isArray(state.items)
                    ? -1
                    : Math.max(0, Math.min(state.items.length - 1, action.index))
            };

        case SET_LANGUAGE:
            if (state.languageId === action.languageId) {
                return state;
            }
            
            return {
                ...state,
                languageId: action.languageId
            };

        default:
            return state;
    }
};

