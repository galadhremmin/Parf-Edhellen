export const SET_FRAGMENTS = 'ED_SET_FRAGMENTS';
export const SET_FRAGMENT_DATA = 'ED_SET_FRAGMENT_DATA';

const EDSentenceAdminReducer = (state = {
    name: '',
    source: '',
    language_id: undefined,
    description: '',
    long_description: '',
    fragments: [],
    id: 0,
    languages: window.EDConfig.languages()
}, action) => {
    switch (action.type) {
        case SET_FRAGMENTS:

            break;
        case SET_FRAGMENT_DATA:

            break;
        default:
            return state;
    }
}

export default EDSentenceAdminReducer;