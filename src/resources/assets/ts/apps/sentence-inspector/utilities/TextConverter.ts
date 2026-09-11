import type {
    FragmentTransformation,
    ISentenceFragmentEntity,
    ISentenceTranslation,
    ITextTransformation,
    ITextTransformationsMap,
} from '@root/connectors/backend/IBookApi';
import { SentenceFragmentType } from '@root/connectors/backend/IBookApi';

import type {
    ILine,
    ILinesReducerState,
    IToken,
} from '../reducers/LinesReducer._types';

/**
 * Words in a single paragraph, past which the whole phrase is set as prose.
 *
 * A phrase of three paragraphs of 27, 43 and 14 words cannot be read as stacked lines:
 * by the second wrap the tengwar and the transcription have lost each other and the
 * reader is matching words by counting. Verse wraps rarely, so it keeps the stacked form.
 */
export const PROSE_AT = 20;

/** A line with no letter in it at all is a divider, not something to read. */
const hasLetters = (text: string) => /\p{L}/u.test(text);

const toTokens = (
    transformation: FragmentTransformation[],
    fragments: ISentenceFragmentEntity[],
    useTransformedText: boolean,
): IToken[] => {
    if (! Array.isArray(transformation)) {
        return [];
    }

    return transformation.map((item) => {
        if (typeof item === 'string') {
            return { fragmentId: 0, text: item };
        }

        const fragment = fragments[item[0]];
        if (fragment === undefined) {
            return { fragmentId: 0, text: '' };
        }

        // The transformation may substitute the text -- that is how the tengwar line gets
        // its glyphs -- but the fragment is what identifies the word either way.
        const text = useTransformedText && item[1] !== undefined
            ? item[1] : fragment.fragment;

        const isWord = fragment.type === SentenceFragmentType.Word && Boolean(fragment.lexicalEntryId);

        return {
            fragmentId: isWord ? (fragment.id || 0) : 0,
            text,
        };
    });
};

/**
 * Builds the lines of a phrase from its transformations, joining the translation to each
 * line **by paragraph number**. The two used to be matched by array position, which held
 * only as long as every paragraph had a translation.
 */
const convertTransformationsToLines = (
    transformations: ITextTransformationsMap,
    translations: ISentenceTranslation[],
    fragments: ISentenceFragmentEntity[],
): ILinesReducerState => {
    const empty: ILinesReducerState = { hasTranslations: false, lines: [], shape: 'verse' };

    if (! transformations) {
        return empty;
    }

    const latin: ITextTransformation = transformations.latin || {};
    const tengwar: ITextTransformation = transformations.tengwar || {};

    const paragraphNumbers = Array.from(
        new Set([ ...Object.keys(latin), ...Object.keys(tengwar) ]),
    ).map((key) => parseInt(key, 10))
        .filter((key) => ! isNaN(key))
        .sort((a, b) => a - b);

    if (paragraphNumbers.length === 0) {
        return empty;
    }

    // A paragraph can carry several translated sentences; they read as one paragraph.
    const translationByParagraph = new Map<number, string[]>();
    for (const translation of translations || []) {
        const existing = translationByParagraph.get(translation.paragraphNumber);
        if (existing) {
            existing.push(translation.translation);
        } else {
            translationByParagraph.set(translation.paragraphNumber, [ translation.translation ]);
        }
    }

    const lines: ILine[] = [];
    let n = 0;

    for (const paragraphNumber of paragraphNumbers) {
        const latinTokens = toTokens(latin[paragraphNumber.toString(10)], fragments, false);
        const tengwarTokens = toTokens(tengwar[paragraphNumber.toString(10)], fragments, true);

        const text = latinTokens.map((token) => token.text).join('');
        if (text.trim() === '') {
            continue;
        }

        if (! hasLetters(text)) {
            lines.push({
                kind: 'rule',
                latin: [],
                n: 0,
                paragraphNumber,
                tengwar: [],
                tengwarByFragment: {},
                translation: null,
                wordCount: 0,
            });
            continue;
        }

        const tengwarByFragment: Record<number, string> = {};
        for (const token of tengwarTokens) {
            if (token.fragmentId) {
                tengwarByFragment[token.fragmentId] = token.text;
            }
        }

        n += 1;
        lines.push({
            kind: 'stanza',
            latin: latinTokens,
            n,
            paragraphNumber,
            tengwar: tengwarTokens,
            tengwarByFragment,
            translation: (translationByParagraph.get(paragraphNumber) || []).join(' ') || null,
            wordCount: latinTokens.filter((token) => token.fragmentId !== 0).length,
        });
    }

    const isProse = lines.some((line) => line.wordCount > PROSE_AT);
    if (isProse) {
        for (const line of lines) {
            if (line.kind === 'stanza') {
                line.kind = 'prose';
            }
        }
    }

    return {
        hasTranslations: lines.some((line) => Boolean(line.translation)),
        lines,
        shape: isProse ? 'prose' : 'verse',
    };
};

export default convertTransformationsToLines;
