/**
 * One renderable piece of a line: either a word that can be opened, or a literal
 * (a space, a comma, a hyphen) that cannot.
 */
export interface IToken {
    /** The fragment this token stands for, or 0 when it is a literal. */
    fragmentId: number;
    text: string;
}

/**
 * `stanza` is a line of verse; `prose` is a paragraph set as flowing text; `rule` is a
 * section break, which the corpus encodes as a run of hyphens in a fragment of its own.
 */
export type LineKind = 'stanza' | 'prose' | 'rule';

export interface ILine {
    kind: LineKind;
    /**
     * Position among the lines that carry text, counted from one. Deliberately not the
     * paragraph number: those are not ordinal (the Moria gate inscription uses 10 and 20,
     * the Túrin wrapper 10 to 120).
     */
    n: number;
    latin: IToken[];
    paragraphNumber: number;
    tengwar: IToken[];
    /**
     * The tengwar of each word, keyed by fragment. Prose sets each word as a small
     * [tengwar / latin] stack, and needs to pair them up without re-scanning the line.
     */
    tengwarByFragment: Record<number, string>;
    translation: string | null;
    wordCount: number;
}

export type PhraseShape = 'verse' | 'prose';

export interface ILinesReducerState {
    hasTranslations: boolean;
    lines: ILine[];
    /**
     * How the whole phrase is set. Chosen from the data rather than per phrase by hand,
     * and never mixed: a document reads as one thing or the other.
     */
    shape: PhraseShape;
}
