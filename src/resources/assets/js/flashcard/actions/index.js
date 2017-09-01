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

export const testCard = (flashcardId, option) => (dispatch, getState) => {
    dispatch({
        type: ED_TEST_CARD
    });

    deferredResolve(axios.post('/dashboard/flashcard/test', {
        flashcard_id: flashcardId,
        translation_id: getState().translation_id,
        translation: option
    }), 800).then(resp => {
        dispatch({
            type: ED_RECEIVE_TRANSLATION,
            correct: resp.data.correct,
            translation: resp.data.translation
        });
    });
};
