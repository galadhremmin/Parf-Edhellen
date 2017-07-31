import { 
    SET_FRAGMENTS,
    SET_SENTENCE_DATA,
    SET_FRAGMENT_DATA,
    SET_TENGWAR
} from '../reducers/admin';
import axios from 'axios';

export const setFragments = fragments => {
    return dispatch => {
        axios.post('/admin/sentence/parse-fragment/latin', { fragments }).then(response => {
            dispatch({
                type: SET_FRAGMENTS,
                fragments,
                latin: response.data
            });
        });
    }
};

export const confirmFragments = () => {
    return (dispatch, getState) => {
        axios.post('/admin/sentence/parse-fragment/tengwar', { fragments: getState().fragments }).then(response => {
            dispatch({
                type: SET_TENGWAR,
                tengwar: response.data
            });
        });
    }
};

export const setSentenceData = data => {
    return {
        type: SET_SENTENCE_DATA,
        data
    };
}

/**
 * Updates the fragments at the specified indexes with the specified data.
 * @param {Number[]} fragmentIndex 
 * @param {Object} data 
 */
export const setFragmentData = (indexes, data) => {

    return {
        type: SET_FRAGMENT_DATA,
        indexes,
        data
    };
};
