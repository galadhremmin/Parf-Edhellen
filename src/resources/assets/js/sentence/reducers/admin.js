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
                        comments:       action.data.comments,
                        tengwar:        action.data.tengwar,
                        inflections:    action.data.inflections
                            .map(inflection => Object.assign({}, inflection))
                    };

                    return newFragment;
                })
            };
        default:
            return state;
    }
}

export default EDSentenceAdminReducer;