import { ParagraphState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';
import { ITranslationRow } from '../components/TranslationForm/TranslationForm._types';
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

export const createTranslationRows = (paragraphs: ParagraphState[], translations: ISentenceTranslationReducerState[]) => {
    const _createKey = (paragraphNumber: number, sentenceNumber: number) => {
        return [paragraphNumber, sentenceNumber].join('|');
    }

    const paragraphNumbers = new Set<number>(
        translations.map((t) => t.paragraphNumber),
    );

    const translationsMap = translations.reduce((map, t) => {
        map.set(_createKey(t.paragraphNumber, t.sentenceNumber), t.translation);
        return map;
    }, new Map());

    // Remove empty paragraphs (the user can create empty paragraphs)
    const paragraphsWithWords = paragraphs.filter(p => p.length);

    if (paragraphNumbers.size !== paragraphsWithWords.length) {
        throw new Error(
            `The number of elements in the paragraphs array (${paragraphs.length}) does not match the number of paragraphs (${paragraphNumbers.size}).`,
        );
    }

    const rows: ITranslationRow[] = [];
    const sentenceMap = new Map<number, string[]>();
    let i = 0;
    for (const paragraphNumber of paragraphNumbers) {
        const paragraph = paragraphsWithWords[i];

        for (const word of paragraph) {
            if (sentenceMap.has(word.sentenceNumber)) {
                sentenceMap.get(word.sentenceNumber).push(word.fragment);
            } else {
                sentenceMap.set(word.sentenceNumber, [word.fragment]);
            }
        }

        for (const sentenceNumber of sentenceMap.keys()) {
            rows.push({
                paragraphNumber,
                sentenceNumber,
                sentenceText: sentenceMap.get(sentenceNumber).join(''),
                translation: translationsMap.get(_createKey(paragraphNumber, sentenceNumber)) || '',
            });
        }

        i += 1;
        sentenceMap.clear();
    }

    return rows;
};
