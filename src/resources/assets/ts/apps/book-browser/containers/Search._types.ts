import type {
    ThunkDispatch,
} from 'redux-thunk';

import type {
    ISearchAction,
} from '../reducers/SearchReducer._types';

export interface IProps {
    currentGlossaryWord: string;
    dispatch: ThunkDispatch<any, any, any>;
    lexicalEntryGroupId?: boolean;
    includeOld?: boolean;
    languageId?: number;
    naturalLanguage?: boolean;
    loading?: boolean;
    reversed?: boolean;
    speechId?: boolean;
    word?: string;
}

export interface IState extends ISearchAction {
    showMore?: boolean;
}
