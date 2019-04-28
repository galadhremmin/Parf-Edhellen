import { IReduxAction } from '@root/_types';
import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export type IGlossState = Partial<IGlossEntity>;

export interface IGlossAction extends IReduxAction {
    gloss: IGlossEntity;
}
