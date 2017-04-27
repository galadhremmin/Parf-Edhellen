export const REQUEST_SUGGESTIONS = 'ED_REQUEST_SUGGESTIONS';
export const RECEIVE_SUGGESTIONS = 'ED_RECEIVE_SUGGESTIONS';
export const SET_FRAGMENTS = 'ED_SET_FRAGMENTS';
export const SET_FRAGMENT_DATA = 'ED_SET_FRAGMENT_DATA';
export const SET_SENTENCE_DATA = 'ED_SET_SENTENCE_DATA';

const EDSentenceAdminReducer = (state = {
    name: '',
    source: '',
    language_id: undefined,
    description: '',
    long_description: '',
    fragments: [],
    id: 0,
    languages: window.EDConfig.languages(),
    loading: false,
    suggestions: undefined
}, action) => {
    switch (action.type) {
        case REQUEST_SUGGESTIONS:
            return {
                ...state,
                loading: true
            };
            break;

        case RECEIVE_SUGGESTIONS:
            return {
                ...state,
                suggestions: action.suggestions,
                loading: false
            };
            break;

        case SET_FRAGMENTS:
            return {
                ...state,
                fragments: action.fragments
            };
            break;

        case SET_SENTENCE_DATA:
            return {
                ...state,
                ...action.data
            };
            break;

        case SET_FRAGMENT_DATA:

            break;
        default:
            return state;
    }
}

export default EDSentenceAdminReducer;