import { IReduxAction } from '@root/_types';
import { IBookGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export type IGlossState = Partial<IBookGlossEntity>;

export interface IGlossAction extends IReduxAction {
    gloss: IBookGlossEntity;
}
