import {
    ED_REQUEST_CARD, 
    ED_RECEIVE_CARD,
    ED_TEST_CARD,
    ED_RECEIVE_GLOSS
} from '../reducers';
import EDAPI from 'ed-api';
import { deferredResolve } from 'ed-promise';

export const getCard = (flashcardId) => (dispatch, getState) => {
    dispatch({
        type: ED_REQUEST_CARD
    });

    deferredResolve(EDAPI.post('/dashboard/flashcard/card', {
      id: flashcardId,
      not: getState().previous_list
    }), 800).then(resp => {
        dispatch({
            type: ED_RECEIVE_CARD,
            ...(resp.data)
        });
    });
};

export const testCard = (flashcardId, option) => (dispatch, getState) => {
    dispatch({
        type: ED_TEST_CARD
    });

    deferredResolve(EDAPI.post('/dashboard/flashcard/test', {
        flashcard_id: flashcardId,
        translation_id: getState().translation_id,
        translation: option
    }), 800).then(resp => {
        dispatch({
            type: ED_RECEIVE_GLOSS,
            correct: resp.data.correct,
            gloss: resp.data.gloss
        });
    });
};
