import EDConfig from 'ed-config';

export const ED_SET_TRANSLATION_DATA = 'ED_SET_TRANSLATION_DATA';
export const ED_REQUEST_TRANSLATION_GROUPS = 'ED_REQUEST_TRANSLATION_GROUPS';
export const ED_RECEIVE_TRANSLATION_GROUPS = 'ED_RECEIVE_TRANSLATION_GROUPS';

const EDTranslationAdminReducer = (state = {
    id: 0,
    account_id: 0,
    word_id: 0,
    word: '',
    translation: '',
    source: '',
    language_id: 0,
    translation_group_id: 0,
    comments: '',
    sense: undefined,
    _keywords: [],
    is_uncertain: false,
    is_rejected: false,
    languages: EDConfig.languages(),
    groups: undefined,
    loading: true
}, action) => {
    switch (action.type) {
        case ED_SET_TRANSLATION_DATA:
            return {
                ...state
            };

        case ED_REQUEST_TRANSLATION_GROUPS:
            return {
                ...state,
                loading: true
            };

        case ED_RECEIVE_TRANSLATION_GROUPS:
            return {
                ...state,
                groups: action.groups,
                loading: false
            };

        default:
            return state;
    }
}

export default EDTranslationAdminReducer;