import type {
    IKeywordEntity,
    ISenseEntity,
    IGlossEntity,
    IWordEntity,
} from '@root/connectors/backend/IGlossResourceApi';
import type { ISenseSelection } from '@root/components/Form/SenseSelect';
import type { ValueTransformer } from './value-transformers._types';

export const defaultTransformer: ValueTransformer<any, any> = (x: any): any => x;

export const keywordsTransformer: ValueTransformer<string[], IKeywordEntity[]> = (keywords) =>
    keywords.map((k) => ({
        word: k,
    }));

export const senseTransformer: ValueTransformer<ISenseSelection, ISenseEntity> = (selection) => ({
        concept: selection.concept,
        conceptId: selection.conceptId,
        id: selection.senseId,
        word: {
            word: selection.sense,
        },
    });

export const glossesTransformer: ValueTransformer<string[], IGlossEntity[]> = (glosses) =>
    glosses.map((t) => ({
        translation: t,
    }));

export const wordTransformer: ValueTransformer<string, IWordEntity> = (word) => ({
        word,
    });
