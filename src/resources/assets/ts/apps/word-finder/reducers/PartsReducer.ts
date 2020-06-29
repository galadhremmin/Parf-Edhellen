import {
    Actions,
    IGameAction,
} from '../actions';
import { IWordPart } from './PartsReducer._types';

const InitialState: IWordPart[] = [];

const PartsReducer = (state = InitialState, action: IGameAction) => {
    switch (action.type) {
        case Actions.InitializeGame: {
            const parts = action.parts.map<IWordPart>((part, i) => ({
                available: true,
                id: 0,
                part,
                selected: false,
            }));

            // https://en.wikipedia.org/wiki/Fisher%E2%80%93Yates_shuffle
            let c = parts.length;
            while (c > 0) {
                const i = Math.floor(Math.random() * c);
                --c;

                const tmp = parts[c];
                parts[c] = parts[i];
                parts[i] = tmp;
            }

            parts.forEach((p, i) => {
                p.id = i;
            });

            return parts;
        }
        case Actions.SelectPart:
            return state.map((part) => {
                if (part.id === action.selectedPartId) {
                    return {
                        ...part,
                        selected: true,
                    };
                }
                return part;
            });
        case Actions.DeselectPart:
            return state.map((part) => {
                if (part.id === action.selectedPartId) {
                    return {
                        ...part,
                        selected: false,
                    };
                }
                return part;
            });
        case Actions.DiscoverWord:
            return state.map((part) => {
                if (part.selected) {
                    return {
                        ...part,
                        available: false,
                        selected: false,
                    };
                }
                return part;
            });
        default:
            return state;
    }
}

export default PartsReducer;
