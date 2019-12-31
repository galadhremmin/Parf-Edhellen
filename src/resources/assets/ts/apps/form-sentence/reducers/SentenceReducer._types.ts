import { IReduxAction } from '@root/_types';
import {
    ISentenceEntity,
} from '@root/connectors/backend/IBookApi';
import { Actions } from '../actions';

export type ISentenceReducerState = Pick<ISentenceEntity, 'account' |
    'description' | 'id' | 'isApproved' | 'isNeologism' | 'language' | 'longDescription' | 'name' | 'source'>;

export interface ISentenceAction extends IReduxAction<Actions> {
    sentence: ISentenceReducerState;
}