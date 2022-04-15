import { IAccountEntity } from '@root/connectors/backend/IBookApi';

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
    hideProfile?: boolean;
    readonly?: boolean;
    statistics?: IAccountStatistics;
}
