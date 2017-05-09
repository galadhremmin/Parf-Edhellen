import EDConfig from 'ed-config';

export const ED_SET_TRANSLATION_DATA = 'ED_SET_TRANSLATION_DATA';

const EDTranslationAdminReducer = (state = {
    id: 0,
    account_id: 0,
    word_id: 0,
    word: '',
    source: '',
    language_id: undefined,
    comments: '',
    is_neologism: false,
    languages: EDConfig.languages(),
    loading: false
}, action) => {
    switch (action.type) {
        case ED_SET_TRANSLATION_DATA:
            return {
                ...state
            };
            break;

        default:
            return state;
    }
}

export default EDTranslationAdminReducer;