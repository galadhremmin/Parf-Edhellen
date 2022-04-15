import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';

export interface IHookedGloss<T extends IBookGlossEntity = IBookGlossEntity> {
    error: string | null;
    gloss: T;
}
