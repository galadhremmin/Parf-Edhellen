import type { ThunkDispatch } from 'redux-thunk';

import type {
    ILexicalEntryEntity,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';
import type { ISectionsState } from '../reducers/SectionsReducer._types';

export interface IEntitiesComponentProps<T = ILexicalEntryEntity> {
    dispatch?: ThunkDispatch<any, any, any>;
    entityMorph?: string;
    groupId?: number;
    groupName?: string;
    isEmpty: boolean;
    /** Every language referenced anywhere on the page (entities + their derivation chains), for id-based lookups — not to be confused with `languages` below. */
    languageDictionary?: ILanguageEntity[];
    /** The "common languages" grouping used for the unusual-languages UI toggle. */
    languages?: ILanguageEntity[];
    loading: boolean;
    sections?: ISectionsState<T>;
    single: boolean;
    word: string;
    unusualLanguages?: ILanguageEntity[];
    forceShowUnusualLanguages?: boolean;
}
