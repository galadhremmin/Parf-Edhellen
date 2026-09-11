import type {
    ILexicalEntryInflection,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';

export interface IFragmentsReducerState {
    /** The gloss of the linked entry, e.g. `star-kindler`. */
    gloss: string;
    comments: string;
    /** The collection the entry belongs to, e.g. `SINDICT`. */
    collection: string;
    fragment: string;
    /**
     * True when an editor has written a note about this word *in this phrase*. Only 179
     * fragments in the corpus carry one and they are the best reading on the page, so the
     * text marks them rather than making the reader hunt.
     */
    hasNote: boolean;
    /** The dictionary form, which is often not the form written in the text. */
    headword: string;
    id: number;
    /**
     * True when the written form differs from the headword -- mutation, plural, tense,
     * possessive. Derived from the join, never a claim about *why* it differs.
     */
    isChanged: boolean;
    lexicalEntryId: number;
    lexicalEntryInflections: ILexicalEntryInflection[];
    nextFragmentId: number;
    paragraphNumber: number;
    previousFragmentId: number;
    sentenceNumber: number;
    source: string;
    speechId: number;
    speech: string;
    tengwar: string;
    type: SentenceFragmentType;
}

export type FragmentsReducerState = IFragmentsReducerState[];
