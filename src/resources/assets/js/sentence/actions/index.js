import axios from 'axios';
import EDConfig from 'ed-config';
import { deferredResolve } from 'ed-promise';
import {
    REQUEST_FRAGMENT,
    RECEIVE_FRAGMENT
} from '../reducers';

export const selectFragment = (fragmentId, translationId) => {
    return dispatch => {
        dispatch({
            type: REQUEST_FRAGMENT,
            fragmentId
        });

        const start = new Date().getTime();
        deferredResolve(axios.get(EDConfig.api(`/book/translate/${translationId}`)), 800)
            .then(resp => {
                dispatch({
                    type: RECEIVE_FRAGMENT,
                    bookData: resp.data,
                    translationId
                });
            });
    };
};