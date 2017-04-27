import axios from 'axios';
import EDConfig from 'ed-config';
import { deferredResolve } from 'ed-promise';
import { 
    REQUEST_SUGGESTIONS,
    RECEIVE_SUGGESTIONS,
    SET_FRAGMENTS,
    SET_FRAGMENT_DATA
} from '../reducers/admin';

export const requestSuggestions = (words, language_id) => {
    return dispatch => {
        dispatch({
            type: REQUEST_SUGGESTIONS
        });

        deferredResolve(axios.post(EDConfig.api('/book/suggest'), { 
            words, 
            language_id
        }), 800).then(resp => {
            dispatch({
                type: RECEIVE_SUGGESTIONS,
                suggestions: resp.data
            });
        });
    }
};

export const setFragments = (fragments) => {
    return {
        type: SET_FRAGMENTS,
        fragments
    };
};

export const setFragmentData = (fragment) => {

};
