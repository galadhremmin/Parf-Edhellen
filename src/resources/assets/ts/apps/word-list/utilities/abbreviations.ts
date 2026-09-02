/**
 * Conventional abbreviations for the parts of speech, as they appear in printed dictionaries.
 *
 * Only unambiguous, well established abbreviations are listed. The database holds compound names
 * such as "preposition/conjunction" for which no accepted short form exists, and inventing one
 * would be worse than simply spelling it out, so anything absent here is rendered in full.
 */
const SPEECH_ABBREVIATIONS: Record<string, string> = {
    'adjective': 'adj.',
    'adverb': 'adv.',
    'article': 'art.',
    'conjunction': 'conj.',
    'infix': 'inf.',
    'interjection': 'interj.',
    'noun': 'n.',
    'ordinal': 'ord.',
    'participle': 'part.',
    'prefix': 'pref.',
    'preposition': 'prep.',
    'pronoun': 'pron.',
    'suffix': 'suf.',
    'verb': 'v.',
};

/**
 * Returns the conventional abbreviation for the given part of speech, or the name unchanged when
 * no established abbreviation exists.
 */
export const abbreviateSpeech = (speech: string): string => {
    if (! speech) {
        return speech;
    }

    return SPEECH_ABBREVIATIONS[speech.toLocaleLowerCase()] ?? speech;
};
