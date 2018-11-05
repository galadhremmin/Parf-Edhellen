import {
    ThunkDispatch,
} from 'redux-thunk';

import {
    ISearchAction,
} from '../reducers/SearchReducer._types';

export interface IProps {
    currentGlossaryWord: string;
    dispatch: ThunkDispatch<any, any, any>;
    includeOld?: boolean;
    languageId?: number;
    reversed?: boolean;
    word?: string;
}

export type IState = ISearchAction;
