import { 
    SET_FRAGMENTS,
    SET_SENTENCE_DATA,
    SET_FRAGMENT_DATA
} from '../reducers/admin';

export const setFragments = fragments => {
    return {
        type: SET_FRAGMENTS,
        fragments
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
