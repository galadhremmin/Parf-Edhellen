import EDConfig from 'ed-config';

export const SET_FRAGMENTS     = 'ED_SET_FRAGMENTS'; 
export const SET_FRAGMENT_DATA = 'ED_SET_FRAGMENT_DATA';
export const SET_SENTENCE_DATA = 'ED_SET_SENTENCE_DATA';
export const SET_TENGWAR       = 'ED_SET_TENGWAR';

const EDSentenceAdminReducer = (state = {
    id: 0,
    account_id: 0,
    name: '',
    source: '',
    language_id: undefined,
    description: '',
    long_description: '',
    is_neologism: false,
    fragments: [],
    latin: [],
    languages: EDConfig.languages(),
    loading: false
}, action) => {
    switch (action.type) {
        case SET_FRAGMENTS:
            return {
                ...state,
                fragments: action.fragments,
                latin: action.latin
            };

        case SET_SENTENCE_DATA:
            return {
                ...state,
                ...action.data
            };

        case SET_FRAGMENT_DATA:
            return {
                ...state,
                fragments: state.fragments.map((f, index) => {
                    if (action.indexes.indexOf(index) === -1) {
                        return f;
                    } 

                    const newFragment = {
                        ...f,
                        translation_id: action.data.translation_id,
                        speech_id:      action.data.speech_id,
                        speech:         action.data.speech,
                        comments:       action.data.comments,
                        tengwar:        action.data.tengwar,
                        type:           action.data.type,
                        inflections:    action.data.inflections
                            .map(inflection => Object.assign({}, inflection))
                    };

                    return newFragment;
                })
            };

        case SET_TENGWAR:
            return {
                ...state,
                tengwar: action.tengwar
            };

        default:
            return state;
    }
}

export default EDSentenceAdminReducer;
