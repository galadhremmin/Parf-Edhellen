import { Actions } from '../actions';
import {
    IEntitiesAction,
    IEntitiesState,
} from './EntitiesReducer._types';

const EntitiesReducer = (state: IEntitiesState = {
    groupId: 0,
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

                groupId: action.groupId,
                single: action.single,
                word: action.word,
            };
        default:
            return state;
    }
};

export default EntitiesReducer;
