import { Actions } from '../actions';
import type {
    IEntitiesAction,
    IEntitiesState,
} from './EntitiesReducer._types';

const EntitiesReducer = (state: IEntitiesState = {
    groupId: 0,
    groupIntlName: null,
    broader: [],
    languages: [],
    loading: false,
    narrower: [],
    pendingWord: null,
    single: false,
    word: '',
}, action: IEntitiesAction): IEntitiesState => {
    switch (action.type) {
        case Actions.RequestEntities:
            return {
                ...state,
                groupId: action.groupId || 0,
                loading: true,
                pendingWord: action.word || null,
            };
        case Actions.ReceiveEntities:
            return {
                ...state,
                loading: false,
                pendingWord: null,

                entityMorph: action.entityMorph || null,
                groupId: action.groupId,
                groupIntlName: action.groupIntlName,
                languages: action.entities.languages || [],
                leadWithUnusual: action.entities.leadWithUnusual || false,
                broader: action.entities.broader || [],
                narrower: action.entities.narrower || [],
                single: action.single,
                word: action.word,
            };
        default:
            return state;
    }
};

export default EntitiesReducer;
