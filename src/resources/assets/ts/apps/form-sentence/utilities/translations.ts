import { ParagraphState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';
import { ITranslationRow } from '../components/TranslationForm/TranslationForm._types';
import {
    ISentenceFragmentEntity,
    ISentenceTranslationEntity,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import { ISentenceTranslationReducerState } from '../reducers/child-reducers/SentenceTranslationReducer._types';
import { ISentenceTranslationsReducerState } from '../reducers/SentenceTranslationsReducer._types';

function groupByParagraphAndSentence<T extends Pick<ISentenceFragmentEntity, 'paragraphNumber' | 'sentenceNumber'>>(fragments: T[]): Map<string, T[]> {
    const group = new Map<string, T[]>();
    if (! Array.isArray(fragments)) {
        return group;
    }

    for (const f of fragments) {
        const key = `${f.paragraphNumber}|${f.sentenceNumber}`;
        if (! group.has(key)) {
            group.set(key, []);
        }
        group.get(key).push(f);
    }

    return group;
}

export function rebuildTranslations(oldTranslations: ISentenceTranslationsReducerState, existingFragments: ISentenceFragmentEntity[], nextFragments: ISentenceFragmentEntity[]) {
    const existingGroup = groupByParagraphAndSentence(existingFragments);
    const existingTranslationGroup = groupByParagraphAndSentence(oldTranslations);
    const nextGroup = groupByParagraphAndSentence(nextFragments);

    const nextTranslations: ISentenceTranslationEntity[] = [];
    for (const groupKey of nextGroup.keys()) {
        const fragments = nextGroup.get(groupKey);

        let nextTranslation: typeof nextTranslations[0];
        if (existingGroup.has(groupKey) && //
            existingTranslationGroup.has(groupKey) && //
            existingGroup.get(groupKey).every((v, i) => v.fragment === fragments[i].fragment)) {
            nextTranslation = existingTranslationGroup.get(groupKey)[0];
        } else {
            const firstFragment = fragments[0];
            nextTranslation = {
                paragraphNumber: firstFragment.paragraphNumber,
                sentenceNumber: firstFragment.sentenceNumber,
                translation: '',
            };
        }

        nextTranslations.push(nextTranslation);
    }

    return nextTranslations;
};

export function createTranslationRows(paragraphs: ParagraphState[], translations: ISentenceTranslationReducerState[]) {
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
