import type { IAccountEntity } from '@root/connectors/backend/IGlossResourceApi';

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
}
