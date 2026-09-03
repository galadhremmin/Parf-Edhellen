/**
 * The placeholder shown before the idle animation kicks in, and whenever the
 * animation is unavailable (reduced motion, or while the field has a value).
 */
export const DefaultPlaceholder = 'What are you looking for?';

interface ISuggestion {
    elvish: string;
    english: string;
}

export interface ISuggestionView {
    /** The word to look up when the suggestion is accepted. */
    query: string;
    /** The line rendered as the placeholder. */
    text: string;
}

/**
 * Well-attested words which double as an invitation to explore the dictionary.
 */
const Suggestions: ISuggestion[] = [
    { elvish: 'mellon',   english: 'friend' },
    { elvish: 'elen',     english: 'star' },
    { elvish: 'melmë',    english: 'love' },
    { elvish: 'galadh',   english: 'tree' },
    { elvish: 'alassë',   english: 'joy' },
    { elvish: 'ithil',    english: 'moon' },
    { elvish: 'estel',    english: 'hope' },
    { elvish: 'lassë',    english: 'leaf' },
    { elvish: 'anar',     english: 'sun' },
    { elvish: 'mîl',      english: 'affection' },
    { elvish: 'súrë',     english: 'wind' },
    { elvish: 'adar',     english: 'father' },
    { elvish: 'telpë',    english: 'silver' },
    { elvish: 'namárië',  english: 'farewell' },
];

/**
 * Builds the suggestion at the specified index. The direction of the example
 * alternates, advertising that the dictionary is searchable in Elvish *and* in
 * English. The quoted word is always the one that is looked up.
 */
export const getSuggestion = (index: number): ISuggestionView => {
    const { elvish, english } = Suggestions[index % Suggestions.length];
    const query = index % 2 === 0 ? elvish : english;
    const gloss = index % 2 === 0 ? english : elvish;

    return {
        query,
        text: `Try “${query}” — ${gloss}`,
    };
};

/**
 * Picks the suggestion the animation begins with, so that returning visitors
 * are not greeted by the same word every time.
 */
export const randomSuggestionIndex = () => Math.floor(Math.random() * Suggestions.length);
