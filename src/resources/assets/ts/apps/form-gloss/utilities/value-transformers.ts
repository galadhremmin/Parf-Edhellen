import {
    IKeywordEntity,
    ITranslationEntity,
    IWordEntity,
} from '@root/connectors/backend/GlossResourceApiConnector._types';
import { ValueTransformer } from './value-transformers._types';

export const defaultTransformer: ValueTransformer<any, any> = (x) => x;

export const wordTransformer: ValueTransformer<string, IWordEntity> = (word) => ({
    word,
});

export const translationsTransformer: ValueTransformer<string[], ITranslationEntity[]> = (translations) =>
    translations.map((t) => ({
        translation: t,
    }));

export const keywordsTransformer: ValueTransformer<string[], IKeywordEntity[]> = (keywords) =>
    keywords.map((k) => ({
        word: k,
    }));
