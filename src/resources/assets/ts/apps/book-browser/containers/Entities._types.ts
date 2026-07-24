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
    /** True when the best overall match lives in an "unusual" language and no normal language has a direct
     * match of its own — the unusual section should then render above the normal one. */
    leadWithUnusual?: boolean;
    loading: boolean;
    sections?: ISectionsState<T>;
    single: boolean;
    word: string;
    unusualLanguages?: ILanguageEntity[];
    forceShowUnusualLanguages?: boolean;
}
