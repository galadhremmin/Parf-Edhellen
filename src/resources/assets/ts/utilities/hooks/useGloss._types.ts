import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';

export interface IHookedGloss<T extends IBookGlossEntity = IBookGlossEntity> {
    error: string | null;
    gloss: T;
}

export interface IGlossHookOptions<T = any> {
    glossAdapter?: (gloss: IBookGlossEntity) => T;
    isEnabled?: boolean;
    isVersion?: boolean;
}
