export const REQUEST_RESULTS    = 'EDSR_REQUEST_RESULTS';
export const REQUEST_NAVIGATION = 'EDSR_REQUEST_NAVIGATION';
export const RECEIVE_RESULTS    = 'EDSR_RECEIVE_RESULTS';
export const RECEIVE_NAVIGATION = 'EDSR_RECEIVE_NAVIGATION';
export const ADVANCE_SELECTION  = 'EDSR_ADVANCE_SELECTION';
export const SET_SELECTION      = 'EDSR_SET_SELECTION';

export const EDSearchResultsReducer = (state = {
    loading: false,
    items: undefined,
    itemIndex: -1,
    word: undefined,
    normalizedWord: undefined
}, action) => {
    switch (action.type) {

        case REQUEST_RESULTS:
            return Object.assign({}, state, {
                loading: true
            });

        case REQUEST_NAVIGATION:
            return Object.assign({}, state, {
                loading: true,
                word: action.word,
                normalizedWord: action.normalizedWord,
                itemIndex: action.index || state.itemIndex
            });

        case RECEIVE_RESULTS:
            return Object.assign({}, state, {
                items: action.items,
                loading: false,
                itemIndex: -1
            });

        case RECEIVE_NAVIGATION:
            return Object.assign({}, state, {
                loading: false
            });

        case ADVANCE_SELECTION:
            return Object.assign({}, state, {
                itemIndex: action.direction < 0
                    ? (state.itemIndex < 1   ? state.items.length - 1 : state.itemIndex - 1)
                    : (state.itemIndex + 1 === state.items.length ? 0 : state.itemIndex + 1)
            });

        case SET_SELECTION:
            return Object.assign({}, state, {
                itemIndex: state.index === -1
                    ? -1
                    : Math.max(0, Math.min(state.items.length - 1, action.index))
            });

        default:
            return state;
    }
};

