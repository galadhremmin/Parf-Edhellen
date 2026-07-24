import type { IDerivationEntity } from '@root/connectors/backend/IBookApi';

export interface IProps {
    /** One entry per competing etymology hypothesis, each an ordered chain from the immediate parent towards the root. */
    derivations: IDerivationEntity[][];
}

export interface ICitation {
    comment: string | null;
    intermediateStages: string[] | null;
    parentForm: string;
    source: string | null;
}

export interface IGroupedChain {
    chain: IDerivationEntity[];
    citations: ICitation[];
}