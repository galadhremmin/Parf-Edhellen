import { ParagraphState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';
import {
    ISentenceFragmentEntity,
    ISentenceTranslationEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import { ISentenceTranslationReducerState } from '../reducers/child-reducers/SentenceTranslationReducer._types';

export const parseTranslations = (fragments: ISentenceFragmentEntity[]) => {
    const translations: ISentenceTranslationEntity[] = [];
    const sentenceParagraphs = new Set<string>();
    const words = fragments.filter((f) => f.type === SentenceFragmentType.Word);
    for (const fragment of words) {
        const {
            sentenceNumber,
            paragraphNumber,
        } = fragment;
        const sentenceParagraph = `${sentenceNumber}.${paragraphNumber}`;
        if (! sentenceParagraphs.has(sentenceParagraph)) {
            translations.push({
                paragraphNumber,
                sentenceNumber,
                translation: '',
            });
            sentenceParagraphs.add(sentenceParagraph);
        }
    }

    return translations;
};

export const buildParagraphSentenceMap = (paragraphs: ParagraphState[], translations: ISentenceTranslationReducerState[]) => {
    const paragraphNumbers = new Set<number>(
        translations.map((t) => t.paragraphNumber),
    );

    if (paragraphNumbers.size !== paragraphs.length) {
        throw new Error(
            `The number of elements in the paragraphs array (${paragraphs.length}) does not match the number of paragraphs (${paragraphNumbers.size}).`,
        );
    }

    const paragraphSentenceMap = new Map<string, string>();
    let i = 0;
    for (const paragraphNumber of paragraphNumbers) {
        const paragraph = paragraphs[i];
        const sentenceMap = new Map<number, string[]>();

        for (const word of paragraph) {
            if (sentenceMap.has(word.sentenceNumber)) {
                sentenceMap.get(word.sentenceNumber).push(word.fragment);
            } else {
                sentenceMap.set(word.sentenceNumber, [word.fragment]);
            }
        }

        for (const sentenceNumber of sentenceMap.keys()) {
            const key = createParagraphSentenceMapKey(paragraphNumber, sentenceNumber);
            if (paragraphSentenceMap.has(key)) {
                throw new Error(`Paragraph and sentence map key collision on ${key}: ${paragraphs}.`);
            }

            paragraphSentenceMap.set(key, sentenceMap.get(sentenceNumber).join(''));
        }

        i += 1;
    }

    return paragraphSentenceMap;
};

export const createParagraphSentenceMapKey = (paragraphNumber: number, sentenceNumber: number) => {
    return `${paragraphNumber}|${sentenceNumber}`;
};
