export const ED_REQUEST_CARD = 'ED_REQUEST_CARDS';
export const ED_RECEIVE_CARD = 'ED_RECEIVE_CARDS';
export const ED_TEST_CARD = 'ED_TEST_CARD';
export const ED_RECEIVE_TRANSLATION = 'ED_RECEIVE_TRANSLATION';

const EDFlashcardReducer = (state = {
  word: '',
  options: [],
  previous_list: [],
  correct: true,
  flip: false,
}, action) => {
    switch (action.type) {
        case ED_REQUEST_CARD:
            return {
                ...state,
                loading: true
            };

        case ED_RECEIVE_CARD:
            return {
                ...state,
                loading: false,
                word: action.word,
                options: action.options,
                translation_id: action.translation_id,
                correct: true,
                flip: false
            };

        case ED_TEST_CARD:
          return {
              ...state,
              loading: true
          };

        case ED_RECEIVE_TRANSLATION:
            // truncate the not list to 100 elements, for those who _really_ into doing flashcards in one go.
            let notList = action.correct ? [...(state.previous_list), state.translation_id] : state.previous_list;
            if (notList.length > 100) {
                notList = notList.slice(notList.length - 100);
            }

            return {
                ...state,
                loading: false,
                flip: true,
                translation: action.translation,
                correct: action.correct,
                previous_list: notList
            };

        default:
            return state;
    }
}

export default EDFlashcardReducer;
