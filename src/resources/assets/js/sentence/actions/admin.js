import { 
    SET_FRAGMENTS,
    SET_SENTENCE_DATA,
    SET_FRAGMENT_DATA,
    SET_TENGWAR
} from '../reducers/admin';
import axios from 'axios';

export const setFragments = fragments => {
    if (fragments !== undefined && (! Array.isArray(fragments) || fragments.length < 1)) {
        return {
            type: SET_FRAGMENTS,
            fragments,
            latin: []
        };
    }

    return (dispatch, getState) => {
        if (fragments === undefined) {
            fragments = getState().fragments;
        }

        const admin = getState().is_admin;
        axios.post(admin ? '/admin/sentence/parse-fragment/latin' 
            : '/dashboard/contribution/sentence/parse-fragment/latin', { fragments }).then(response => {
            dispatch({
                type: SET_FRAGMENTS,
                fragments,
                latin: response.data
            });
        });
    }
};

export const setTengwar = tengwar => {
    return {
        type: SET_TENGWAR,
        tengwar
    };
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
