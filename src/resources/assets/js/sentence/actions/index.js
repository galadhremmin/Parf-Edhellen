import EDAPI from 'ed-api';
import { deferredResolve } from 'ed-promise';
import {
    REQUEST_FRAGMENT,
    RECEIVE_FRAGMENT
} from '../reducers';

export const selectFragment = (fragmentId, glossId) => {
    return dispatch => {
        dispatch({
            type: REQUEST_FRAGMENT,
            fragmentId
        });

        const start = new Date().getTime();
        deferredResolve(EDAPI.get(`book/translate/${glossId}`), 800)
            .then(resp => {
                dispatch({
                    type: RECEIVE_FRAGMENT,
                    bookData: resp.data,
                    glossId
                });
            });
    };
};
