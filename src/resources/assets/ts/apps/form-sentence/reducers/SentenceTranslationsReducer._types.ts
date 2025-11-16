import type {
    ISentenceTranslationAction,
    ISentenceTranslationReducerState,
} from './child-reducers/SentenceTranslationReducer._types';

export type ISentenceTranslationsReducerState = ISentenceTranslationReducerState[];

export interface ISentenceTranslationsAction extends ISentenceTranslationAction {
    sentenceTranslations: ISentenceTranslationsReducerState;
}
