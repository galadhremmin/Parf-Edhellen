import type { IReduxAction } from '@root/_types/redux';
import type {
    ISentenceEntity,
    ISentenceResponse,
} from '@root/connectors/backend/IBookApi';

export type ISentenceReducerState = ISentenceEntity;

export interface ISentenceReducerAction extends IReduxAction {
    id?: number;
    sentence?: ISentenceResponse;
}
