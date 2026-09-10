import { Actions } from '../actions';
import convertTransformationsToLines from '../utilities/TextConverter';
import type { ILinesReducerState } from './LinesReducer._types';
import type { ISentenceReducerAction } from './SentenceReducer._types';

const defaultState: ILinesReducerState = {
    hasTranslations: false,
    lines: [],
    shape: 'verse',
};

const LinesReducer = (state: ILinesReducerState = defaultState, action: ISentenceReducerAction) => {
    switch (action.type) {
        case Actions.ReceiveSentence:
            return convertTransformationsToLines(
                action.sentence.sentenceTransformations,
                action.sentence.sentenceTranslations,
                action.sentence.sentenceFragments,
            );
        default:
            return state;
    }
};

export default LinesReducer;
