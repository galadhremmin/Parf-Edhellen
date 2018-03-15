export const ED_SET_GLOSS_DATA       = 'ED_SET_GLOSS_DATA';
export const ED_REQUEST_GLOSS_GROUPS = 'ED_REQUEST_GLOSS_GROUPS';
export const ED_RECEIVE_GLOSS_GROUPS = 'ED_RECEIVE_GLOSS_GROUPS';
export const ED_COMPONENT_IS_READY   = 'ED_COMPONENT_IS_READY';

const EDGlossAdminReducer = (state = {
    id: 0,
    account_id: 0,
    word_id: 0,
    word: '',
    tengwar: '',
    translations: '',
    source: '',
    language_id: 0,
    speech_id: 0,
    gloss_group_id: 0,
    comments: '',
    notes: '',
    sense: undefined,
    is_uncertain: false,
    is_rejected: false,
    languages: {},
    groups: undefined,
    loading: true,
    _keywords: [],
    contribution_id: undefined
}, action) => {
    switch (action.type) {
        case ED_SET_GLOSS_DATA:
            return {
                ...state
            };

        case ED_REQUEST_GLOSS_GROUPS:
            return {
                ...state,
                loading: true
            };

        case ED_RECEIVE_GLOSS_GROUPS:
            return {
                ...state,
                groups: action.groups,
                loading: false
            };
        
        case ED_COMPONENT_IS_READY:
            return {
                ...state,
                loading: false
            };

        default:
            return state;
    }
}

export default EDGlossAdminReducer;
