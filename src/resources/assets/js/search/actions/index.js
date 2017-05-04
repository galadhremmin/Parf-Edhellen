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

export function requestResults(wordSearch) {
    return {
        type: REQUEST_RESULTS,
        wordSearch
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

export function fetchResults(word, reversed = false, languageId = 0) {
    if (!word || /^\s$/.test(word)) {
        return;
    }

    return dispatch => {
        dispatch(requestResults(word));
        axios.post(EDConfig.api('/book/find'), { 
            word, 
            reversed, 
            language_id: languageId 
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
    const title = `${word} - Parf Edhellen`;

    // When navigating using the browser's back and forward buttons,
    // the state needn't be modified.
    if (modifyState) {
        window.history.pushState(null, title, address);
    }

    // because most browsers doesn't change the document title when pushing state
    document.title = title;

    // Inform indirect listeners about the navigation
    const event = new CustomEvent('ednavigate', { detail: { address, word } });
    window.dispatchEvent(event);

    return dispatch => {
        dispatch(requestNavigation(word, normalizedWord || undefined, index));

        axios.post(apiAddress, { word: normalizedWord || word }).then(resp => {
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
