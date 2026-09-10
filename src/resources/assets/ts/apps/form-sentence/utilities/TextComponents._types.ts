/**
 * The paragraph model used by the sentence contribution form.
 *
 * This used to live in the sentence-inspector app, which shared it with the reader. The
 * reader now builds lines rather than paragraphs of loose fragments, so the model belongs
 * to the form that still uses it: the contribution flow renders a transformation back into
 * editable text, which is a different job from setting a phrase for reading.
 */
export interface IFragmentInSentenceState {
    id: number;
    fragment: string;
    sentenceNumber: number;
}

export type ParagraphState = IFragmentInSentenceState[];

export interface ITextState {
    paragraphs: ParagraphState[];
    transformerName: string;
}
