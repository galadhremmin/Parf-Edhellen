import { IReduxAction } from '@root/_types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import { Actions } from '../../actions';

export type ISentenceFragmentReducerState = Pick<ISentenceFragmentEntity, 'comments' |
    'fragment' | 'glossId' | 'sentenceNumber' | 'speechId' | 'tengwar' | 'type' | 'id' |
    'glossInflections' | 'paragraphNumber'> & {
        _error?: string[];
    };

export interface ISentenceFragmentAction<T extends keyof ISentenceFragmentReducerState = keyof ISentenceFragmentReducerState> extends IReduxAction<Actions> {
    field: T;
    sentenceFragment: ISentenceFragmentReducerState;
    value: ISentenceFragmentReducerState[T];
}
