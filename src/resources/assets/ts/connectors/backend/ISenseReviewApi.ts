/** One meaning a sense could have, as the taxonomy describes it. */
export interface IReviewCandidate {
    definition: string;
    label: string;
    /** WordNet's broad category, e.g. noun.plant. */
    lexname: string;
    /** What it is a kind of, nearest first: tree, woody plant. */
    lineage: string[];
    pos: string;
    synonyms: string[];
    synsetId: string;
}

/** One entry glossed with the sense, and where to read it in the dictionary. */
export interface IReviewUsage {
    /** The glosses that add to the sense, the ones that only restate it having been dropped. */
    glosses: string;
    language: string;
    lexicalEntryId: number;
    speech: string | null;
    url: string;
    word: string;
}

export interface ISenseUnderReview {
    candidates: IReviewCandidate[];
    /** How sure the judge was, when one answered at all. */
    confidence: number | null;
    /** What the judge answered, when it answered but could not be trusted. */
    detail: string | null;
    /** How many entries are glossed with this sense. */
    entries: number;
    /** The word its meanings were looked up under. */
    headword: string;
    reason: string;
    sense: string;
    senseId: number;
    /** The parts of speech its entries record. */
    speeches: string[];
    /** The first few entries glossed with it. */
    usages: IReviewUsage[];
    /** The candidates belong to the head of a phrase, so the sense is a kind of one of them. */
    viaPhraseHead: boolean;
}

export interface INextSenseResponse {
    byReason: Record<string, number>;
    sense: ISenseUnderReview | null;
    waiting: number;
}

export interface IDecision {
    /** A meaning the editor went looking for, rather than one on offer. */
    conceptId?: number;
    /** The sense means nothing the taxonomy can hold: a name, a grammatical label. */
    dismiss?: boolean;
    /** The meanings the page showed, so the log records the offer as well as the answer. */
    offered?: string[];
    /** Whether the sense means the chosen meaning, or is a kind of it. */
    relation?: 'synonym' | 'kind_of';
    /** One of the meanings on offer. */
    synsetId?: string;
}

export interface IRewordResult {
    /** How many entries moved to the corrected wording. */
    entries: number;
    /** Why nothing moved, when nothing did. */
    refusal: string | null;
    sense: string | null;
    senseId: number | null;
}

export interface IRewordResponse {
    outcome: string;
    result: IRewordResult;
    waiting: number;
}

export interface IDecisionResponse {
    assigned: boolean;
    outcome: string;
    waiting: number;
}

export default interface ISenseReviewApi {
    /**
     * The next sense waiting for a decision.
     *
     * @param skip senses passed over in this sitting, which the queue should step past.
     */
    next(skip?: number[], reason?: string): Promise<INextSenseResponse>;

    /**
     * Records what an editor decided about a sense.
     */
    decide(senseId: number, decision: IDecision): Promise<IDecisionResponse>;

    /**
     * Corrects the wording itself: every entry glossed with the sense moves to the one given.
     */
    reword(senseId: number, sense: string): Promise<IRewordResponse>;
}
