import {
    IKeywordEntity,
    ISenseEntity,
    IGlossEntity,
    IWordEntity,
} from '@root/connectors/backend/IGlossResourceApi';
import { ValueTransformer } from './value-transformers._types';

export const defaultTransformer: ValueTransformer<any, any> = (x) => x;

export const keywordsTransformer: ValueTransformer<string[], IKeywordEntity[]> = (keywords) =>
    keywords.map((k) => ({
        word: k,
    }));

export const senseTransformer: ValueTransformer<string, ISenseEntity> = (word) => ({
        word: {
            word,
        },
    });

export const glossesTransformer: ValueTransformer<string[], IGlossEntity[]> = (glosses) =>
    glosses.map((t) => ({
        translation: t,
    }));

export const wordTransformer: ValueTransformer<string, IWordEntity> = (word) => ({
        word,
    });
