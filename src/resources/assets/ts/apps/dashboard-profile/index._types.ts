import type { IWelcome } from '@root/connectors/backend/IAccountApi';
import type { IAccountEntity } from '@root/connectors/backend/IGlossResourceApi';

import type { IProfileWordList } from './components/ProfileWordLists._types';

export interface IAccountStatistics {
    noOfFlashcards?: number;
    noOfGlosses?: number;
    noOfPosts?: number;
    noOfSentences?: number;
    noOfThanks?: number;
    noOfWords?: number;
}

export interface IProps {
    account: IAccountEntity;
    container: string;
    readonly?: boolean;
    showJumbotron?: boolean;
    showProfile?: boolean;
    showProfileLink?: boolean;
    showDiscuss?: boolean;
    statistics?: IAccountStatistics;
    wordLists?: IProfileWordList[];
    /** Only on your own profile, while there's still something to do. */
    welcome?: IWelcome | null;
    /** On your own profile after hiding the welcome: how many steps are left, so it can be offered back. */
    welcomePending?: number;
}

export type { IWelcome, IWelcomeStep } from '@root/connectors/backend/IAccountApi';
