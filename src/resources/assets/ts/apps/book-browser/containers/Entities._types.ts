import { ThunkDispatch } from 'redux-thunk';

import { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { IGlossesState } from '../reducers/GlossesReducer._types';
import { SentenceReducerState } from '../reducers/SentencesReducer._types';

export interface IEntitiesComponentProps {
    dispatch?: ThunkDispatch<any, any, any>;
    groupId?: number;
    groupName?: string;
    isEmpty: boolean;
    loading: boolean;
    single: boolean;
    word: string;

    // Glossary
    glosses?: IGlossesState;
    languages?: ILanguageEntity[];
    unusualLanguages?: ILanguageEntity[];

    // Sentences
    sentences?: SentenceReducerState;
}
