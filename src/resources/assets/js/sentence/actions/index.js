import axios from 'axios';
import {
    REQUEST_FRAGMENT,
    RECEIVE_FRAGMENT
} from '../reducers';

export function selectFragment(fragmentId, translationId) {
    return dispatch => {
        dispatch({
            type: REQUEST_FRAGMENT,
            fragmentId
        });

        const start = new Date().getTime();
        axios.get(window.EDConfig.api(`/book/translate/${translationId}`))
            .then(resp => {
                // Enable the animation to play at least 800 milliseconds.
                const animationDelay = -Math.min(0, (new Date().getTime() - start) - 800);

                window.setTimeout(() => {
                    dispatch({
                        type: RECEIVE_FRAGMENT,
                        bookData: resp.data,
                        translationId
                    });
                }, animationDelay);
            });
    };
}

