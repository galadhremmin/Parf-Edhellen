import {
    ED_REQUEST_CARD, 
    ED_RECEIVE_CARD,
    ED_TEST_CARD,
    ED_RECEIVE_TRANSLATION
} from '../reducers';
import axios from 'axios';
import EDConfig from 'ed-config';
import { deferredResolve } from 'ed-promise';

export const getCard = (flashcardId) => (dispatch, getState) => {
    dispatch({
        type: ED_REQUEST_CARD
    });

    deferredResolve(axios.post('/dashboard/flashcard/card', {
      id: flashcardId,
      not: getState().previous_list
    }), 800).then(resp => {
        dispatch({
            type: ED_RECEIVE_CARD,
            ...(resp.data)
        });
    });
};

export const testCard = (option) => (dispatch, getState) => {
    dispatch({
        type: ED_TEST_CARD
    });

    deferredResolve(axios.get(EDConfig.api(`/book/translate/${getState().translation_id}`)), 800).then(resp => {
        const translation = resp.data.sections[0].glosses[0];
        dispatch({
            type: ED_RECEIVE_TRANSLATION,
            correct: translation.translation === option,
            translation,
        });
    });
};