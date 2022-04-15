import { Actions } from '../actions';
import {
    IEntitiesAction,
    IEntitiesState,
} from './EntitiesReducer._types';

const EntitiesReducer = (state: IEntitiesState = {
    groupId: 0,
    groupIntlName: null,
    loading: false,
    single: false,
    word: '',
}, action: IEntitiesAction): IEntitiesState => {
    switch (action.type) {
        case Actions.RequestEntities:
            return {
                ...state,
                groupId: action.groupId || 0,
                loading: true,
            };
        case Actions.ReceiveEntities:
            return {
                ...state,
                loading: false,

                entityMorph: action.entityMorph || null,
                groupId: action.groupId,
                groupIntlName: action.groupIntlName,
                single: action.single,
                word: action.word,
            };
        default:
            return state;
    }
};

export default EntitiesReducer;
