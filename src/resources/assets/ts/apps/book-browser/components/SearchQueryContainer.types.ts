import {
    ThunkDispatch,
} from 'redux-thunk';

import {
    ISearchActionState,
} from '../actions/SearchActions.types';

export interface IProps {
    dispatch: ThunkDispatch<any, any, any>;
}

export type IState = ISearchActionState;
