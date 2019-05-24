import { IReduxAction } from '@root/_types';
import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';

export type IGlossState = IGlossEntity;

export interface IGlossAction extends IReduxAction {
    gloss: IGlossEntity;
    field: string;
    value: string;
}