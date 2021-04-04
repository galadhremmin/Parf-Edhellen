import { Actions } from '../actions';
import { IEntitiesAction } from './EntitiesReducer._types';
import { IMatchedSentence, SentenceReducerState } from './SentencesReducer._types';

const SentenceReducerIntlName = 'sentence';

const SentencesReducer = (state: SentenceReducerState, action: IEntitiesAction<IMatchedSentence>) => {
    if (SentenceReducerIntlName !== action.groupIntlName) {
        return state;
    }

    switch (action.type) {
        case Actions.ReceiveEntities:
            return action.entities;
        default:
            return state;
    }
};

export default SentencesReducer;
