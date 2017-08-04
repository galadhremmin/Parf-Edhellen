import axios from 'axios';
import EDConfig from 'ed-config';
import {
    REQUEST_RESULTS,
    REQUEST_NAVIGATION,
    RECEIVE_RESULTS,
    RECEIVE_NAVIGATION,
    ADVANCE_SELECTION,
    SET_SELECTION
} from '../reducers';

export function requestResults(wordSearch, reversed, languageId) {
    return {
        type: REQUEST_RESULTS,
        wordSearch,
        reversed,
        languageId
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

export function receiveNavigation(bookData) {
    return {
        type: RECEIVE_NAVIGATION,
        bookData
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

export function fetchResults(word, reversed = false, language_id = 0) {
    if (!word || /^\s$/.test(word)) {
        return;
    }

    return dispatch => {
        dispatch(requestResults(word, reversed, language_id));
        axios.post(EDConfig.api('/book/find'), { 
            word, 
            reversed, 
            language_id 
        }).then(resp => {
            const results = resp.data.map(r => ({
                word: r.k,
                normalizedWord: r.nk
            }));

            dispatch(receiveResults(results));
        });
    }
}

export function beginNavigation(word, normalizedWord, index, modifyState) {
    if (modifyState === undefined) {
        modifyState = true;
    }

    if (!index && index !== 0) {
        index = undefined;
    }

    const uriEncodedWord = encodeURIComponent(normalizedWord || word);
    const apiAddress = EDConfig.api('/book/translate');
    const address = '/w/' + uriEncodedWord;
    const capitalTitle = word.split(' ').map(w => w.substr(0, 1).toLocaleUpperCase() + w.substr(1)).join(' ');
    const title = `${capitalTitle} - Parf Edhellen`;

    // When navigating using the browser's back and forward buttons,
    // the state needn't be modified.
    if (modifyState) {
        if (window.history.pushState !== undefined) {
            window.history.pushState(null, title, address);
        } else {
            // If pushState isn't supported, do not even pretend to try to load react components for search results 
            // for this deprecated browser.
            window.setTimeout(() => window.location.href = address, 0);
            return () => {};
        }
    }

    // because most browsers doesn't change the document title when pushing state
    document.title = title;

    return (dispatch, getState) => {

        // Retrieve language filter configuration
        const language_id = getState().languageId || undefined;

        // Inform indirect listeners about the navigation
        const event = new CustomEvent('ednavigate', { detail: { address, word, language_id } });
        window.dispatchEvent(event);

        dispatch(requestNavigation(word, normalizedWord || undefined, index));

        axios.post(apiAddress, { 
            word: normalizedWord || word, 
            language_id,
            inflections: true 
        }).then(resp => {
            dispatch(receiveNavigation(resp.data));

            // Find elements which is requested to be deleted upon receiving the navigation commmand
            const elementsToDelete = document.querySelectorAll('.ed-remove-when-navigating');
            if (elementsToDelete.length > 0) {
                for (let element of elementsToDelete) {
                    element.parentNode.removeChild(element);
                }
            }
        });
    };
}
