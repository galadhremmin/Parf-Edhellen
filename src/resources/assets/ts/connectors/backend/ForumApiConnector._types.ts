import { IAccountEntity } from './BookApiConnector._types';

export interface IPostEntity {
    account: IAccountEntity;
    content: string;
    createdAt: string;
    id: number;
    isDeleted: boolean;
    numberOfLikes?: number;
    parentForumPostId: number;
}
