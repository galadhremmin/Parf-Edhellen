import type { IDerivationEntity, IPhoneticDevelopmentEntity } from '@root/connectors/backend/IBookApi';

export interface IProps {
    /** Used to compose each row's "Development" summary and look up its source citation, both keyed by groupUuid — see LexicalEntryPhoneticDevelopments.tsx. */
    derivations: IDerivationEntity[][];
    /** One entry per attested/reconstructed form chain, each ordered from earliest form to the modern word. */
    phoneticDevelopments: IPhoneticDevelopmentEntity[][];
    /** The dictionary entry's own spelled headword (e.g. "galadh") — the target of the "Development" summary, distinct from the phonetic chain's raw last stage (e.g. "galað"). */
    word: string;
}

export interface IGroupedRow {
    chain: IPhoneticDevelopmentEntity[];
    developmentSteps: string[];
    sources: string[];
}
