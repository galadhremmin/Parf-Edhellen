import type ISenseApi from '@root/connectors/backend/ISenseApi';
import type { IConceptSuggestion } from '@root/connectors/backend/ISenseApi';

import type { ComponentEventHandler } from '../../Component._types';

/**
 * What the picker settles on: the wording readers see, optionally the sense it joins, and optionally what it means.
 */
export interface ISenseSelection {
    /** The meaning, when one was chosen. Carried for display; only its ID is submitted. */
    concept?: IConceptSuggestion;
    conceptId?: number;
    sense: string;
    /** The sense it joins, when an existing wording was chosen rather than typed. */
    senseId?: number;
}

export interface IProps extends ISenseSelection {
    id?: string;
    name?: string;
    required?: boolean;
    senseApi?: ISenseApi;
    onChange: ComponentEventHandler<ISenseSelection>;
}
