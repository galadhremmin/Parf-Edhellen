import { IReduxAction } from '@root/_types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import { Actions } from '../../actions';

export type ISentenceFragmentReducerState = Pick<ISentenceFragmentEntity, 'comments' |
    'fragment' | 'glossId' | 'sentenceNumber' | 'speechId' | 'tengwar' | 'type' | 'id'>;

export interface ISentenceFragmentAction extends IReduxAction<Actions> {
    sentenceFragment: ISentenceFragmentReducerState;
}
