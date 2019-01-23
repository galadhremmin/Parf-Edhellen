import { Actions } from '../actions';

const ThreadReducer = (state: any = {}, action: any) => {
    switch (state.type) {
        case 'TODO':
            return state;
        default:
            return state;
    }
};

export default ThreadReducer;
