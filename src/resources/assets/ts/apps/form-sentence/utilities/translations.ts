import {
    ISentenceFragmentEntity,
    ISentenceTranslationEntity,
} from '@root/connectors/backend/IBookApi';

export const parseTranslations = (fragments: ISentenceFragmentEntity[]) => {
    const translations: ISentenceTranslationEntity[] = [];
    const sentenceParagraphs = new Set<string>();

    for (const fragment of fragments) {
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
