import { ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { IForumGroupEntity } from '@root/connectors/backend/IDiscussApi';

export type ICategoryState = ILanguageEntity | IForumGroupEntity;

export interface ICategoriesState {
    common: ICategoryState[];
    unusual: ICategoryState[];
    isEmpty: boolean;
}