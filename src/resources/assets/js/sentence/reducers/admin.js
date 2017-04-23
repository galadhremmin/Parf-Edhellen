export const SET_FRAGMENTS = 'ED_SET_FRAGMENTS';
export const SET_FRAGMENT_DATA = 'ED_SET_FRAGMENT_DATA';

const EDSentenceAdminReducer = (state = {
    fragments: []
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