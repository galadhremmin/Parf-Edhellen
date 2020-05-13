import {
    ThunkDispatch,
} from 'redux-thunk';

import {
    ISearchAction,
} from '../reducers/SearchReducer._types';

export interface IProps {
    currentGlossaryWord: string;
    dispatch: ThunkDispatch<any, any, any>;
    glossGroupId?: boolean;
    includeOld?: boolean;
    languageId?: number;
    loading?: boolean;
    reversed?: boolean;
    speechId?: boolean;
    word?: string;
}

export interface IState extends ISearchAction {
    showMore?: boolean;
}
