import {
    Actions,
    IGameAction,
} from '../actions';
import { IGameGloss } from './IGlossesReducer';

const InitialState: IGameGloss[] = [];

const GlossesReducer = (state = InitialState, action: IGameAction) => {
    switch (action.type) {
        case Actions.InitializeGame:
            return action.glossary.map((g) => ({
                available: true,
                gloss: g.gloss,
                id: g.id,
                word: g.word.replace(/[\-]/g, ''),
            }) as IGameGloss);
        case Actions.DiscoverWord:
            return state.map((g) => {
                if (g.id === action.glossId) {
                    return {
                        ...g,
                        available: false,
                    } as IGameGloss;
                }
                return g;
            });
        default:
            return state;
    }
};

export default GlossesReducer;
