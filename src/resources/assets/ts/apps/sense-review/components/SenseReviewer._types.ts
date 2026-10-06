import type ISenseApi from '@root/connectors/backend/ISenseApi';
import type ISenseReviewApi from '@root/connectors/backend/ISenseReviewApi';
import type { IReviewCandidate, ISenseUnderReview } from '@root/connectors/backend/ISenseReviewApi';

export type Relation = 'synonym' | 'kind_of';

export interface IProps {
    api?: ISenseReviewApi;
    /** How many senses wait behind each reason, as the page was rendered. */
    byReason?: Record<string, number>;
    /** What each reason means, in a sentence. */
    reasons?: Record<string, string>;
    senseApi?: ISenseApi;
    waiting?: number;
}

export interface ICandidateProps {
    candidate: IReviewCandidate;
    disabled: boolean;
    /** Its place in the list, which is also its keyboard shortcut. */
    ordinal: number;
    onChoose: (synsetId: string, relation: Relation) => void;
    /** The candidates are meanings of a phrase's head word, so the sense is a kind of one of them. */
    viaPhraseHead: boolean;
}

export interface IMeaningSearchProps {
    disabled: boolean;
    /** The word the sense is keyed by, named in the placeholder so the editor knows what to describe. */
    headword: string;
    senseApi?: ISenseApi;
    onChoose: (conceptId: number) => void;
}

export interface IRewordProps {
    disabled: boolean;
    /** How many entries would move, which the button says plainly before it is pressed. */
    entries: number;
    senseApi?: ISenseApi;
    /** The wording these entries carry now, so the button stays shut until a different one is given. */
    wording: string;
    onReword: (wording: string) => void;
}

export interface IEvidenceProps {
    sense: ISenseUnderReview;
    /** What each reason means, in a sentence. */
    reasons: Record<string, string>;
}
