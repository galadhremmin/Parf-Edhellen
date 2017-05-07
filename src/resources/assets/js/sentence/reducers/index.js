export const REQUEST_FRAGMENT = 'EDSR_REQUEST_FRAGMENT';
export const RECEIVE_FRAGMENT  = 'EDSR_RECEIVE_FRAGMENT';

const EDSentenceReducer = (state = {
    fragments: JSON.parse(document.getElementById('ed-preload-fragments').textContent),
    fragmentId: undefined,
    bookData: undefined,
    loading: false
}, action) => {
    switch (action.type) {

        case REQUEST_FRAGMENT:
            return {
                ...state,
                fragmentId: action.fragmentId,
                loading: true
            };

        case RECEIVE_FRAGMENT:
            return {
                ...state,
                translationId: action.translationId,
                bookData: action.bookData,
                loading: false
            };

        default:
            return state;
    }
};

export default EDSentenceReducer;
