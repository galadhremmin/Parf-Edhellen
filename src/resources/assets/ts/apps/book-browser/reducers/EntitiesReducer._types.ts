import type { IGlossaryResponse, ILanguageEntity, IRelatedConcept } from '@root/connectors/backend/IBookApi';
import type { IReduxAction } from '@root/_types';

export interface IEntitiesAction<T = IGlossaryResponse> extends IReduxAction {
    entities: T;
    entityMorph: string;
    groupId: number;
    groupIntlName: string;
    single: boolean;
    word: string;
}

export interface IEntitiesState {
    entityMorph?: string;
    /** The word being fetched, known from the moment it is tapped. Lets the
      * glossary show the real headword while the rest is still in flight. */
    pendingWord?: string;
    groupId: number;
    groupIntlName: string;
    languages: ILanguageEntity[];
    leadWithUnusual?: boolean;
    loading: boolean;
    /** What the word that was searched for is a kind of. */
    broader?: IRelatedConcept[];
    /** The kinds of the word that was searched for. */
    narrower?: IRelatedConcept[];
    single: boolean;
    word: string;
}
