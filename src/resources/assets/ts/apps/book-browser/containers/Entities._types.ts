import { ThunkDispatch } from 'redux-thunk';

import {
    ILexicalEntryEntity,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';
import { ISectionsState } from '../reducers/SectionsReducer._types';

export interface IEntitiesComponentProps<T = ILexicalEntryEntity> {
    dispatch?: ThunkDispatch<any, any, any>;
    entityMorph?: string;
    groupId?: number;
    groupName?: string;
    isEmpty: boolean;
    languages?: ILanguageEntity[];
    loading: boolean;
    sections?: ISectionsState<T>;
    single: boolean;
    word: string;
    unusualLanguages?: ILanguageEntity[];
    forceShowUnusualLanguages?: boolean;
}
