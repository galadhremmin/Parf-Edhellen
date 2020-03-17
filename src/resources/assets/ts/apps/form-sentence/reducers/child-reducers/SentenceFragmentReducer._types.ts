import { IReduxAction } from '@root/_types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import { Actions } from '../../actions';

export type ISentenceFragmentReducerState = Pick<ISentenceFragmentEntity, 'comments' |
    'fragment' | 'glossId' | 'sentenceNumber' | 'speechId' | 'tengwar' | 'type' | 'id' |
    'inflections' | 'paragraphNumber'>;

export interface ISentenceFragmentAction<T extends keyof ISentenceFragmentEntity = keyof ISentenceFragmentEntity> extends IReduxAction<Actions> {
    field: T;
    sentenceFragment: ISentenceFragmentReducerState;
    value: ISentenceFragmentEntity[T];
}
