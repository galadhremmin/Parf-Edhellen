import { IGloss } from '@root/connectors/backend/IWordFinderApi';

export interface IGameGloss extends IGloss {
    available: boolean;
    wordForComparison: string;
}
