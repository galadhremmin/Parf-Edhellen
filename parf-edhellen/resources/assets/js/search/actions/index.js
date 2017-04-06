import axios from 'axios';
import {
    REQUEST_RESULTS,
    REQUEST_NAVIGATION,
    RECEIVE_RESULTS,
    RECEIVE_NAVIGATION,
    ADVANCE_SELECTION,
    SET_SELECTION
} from '../reducers';

export function requestResults() {
    return {
        type: REQUEST_RESULTS
    };
}

export function receiveResults(results) {
    return {
        type: RECEIVE_RESULTS,
        items: results
    };
}

export function requestNavigation(word, normalizedWord, index) {
    return {
        type: REQUEST_NAVIGATION,
        word,
        normalizedWord,
        index
    };
}

export function receiveNavigation(data) {
    return {
        type: RECEIVE_NAVIGATION
    };
}

export function advanceSelection(direction) {
    return {
        type: ADVANCE_SELECTION,
        direction: direction > 0 ? 1 : -1
    };
}

export function setSelection(index) {
    return {
        type: SET_SELECTION,
        index
    };
}

export function fetchResults(word, reversed = false, languageId = 0) {
    if (!word || /^\s$/.test(word)) {
        return;
    }

    return dispatch => {
        dispatch(requestResults());
        axios.post('/api/v1/book/find', { word, reversed, languageId }).then(resp => {
            const results = resp.data.map(r => ({
                word: r.k,
                normalizedWord: r.nk
            }));

            dispatch(receiveResults(results));
        });
    }
}

export function beginNavigation(word, normalizedWord, index, modifyState) {
    // TODO: TEMPORARY! Must be replaced with a React components that are not vulnerable to XSS ...
    if (modifyState === undefined) {
        modifyState = true;
    }

    const address = '/w/' + encodeURIComponent(normalizedWord || word);
    const title = `${word} - Parf Edhellen`;

    if (modifyState) {
        window.history.pushState(null, title, address);
    }
    document.title = title; // because most browsers doesn't change the document title when pushing state

    return dispatch => {
        dispatch(requestNavigation(word, normalizedWord || undefined, index || undefined));

        axios.get(address, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // this is important for the controller!
            }
        }).then(resp => {
            document.getElementById('result').innerHTML = resp.data; // Warning... vulnerable to XSS!
            dispatch(receiveNavigation(resp.data));
        });
    };
}
