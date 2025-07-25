import { IReduxAction } from '@root/_types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import { Actions } from '../../actions';

export type ISentenceFragmentReducerState = Pick<ISentenceFragmentEntity, 'comments' |
    'fragment' | 'lexicalEntryId' | 'sentenceNumber' | 'speechId' | 'tengwar' | 'type' | 'id' |
    'lexicalEntryInflections' | 'paragraphNumber'> & {
        _error?: string[];
    };

export interface ISentenceFragmentAction<T extends keyof ISentenceFragmentReducerState = keyof ISentenceFragmentReducerState> extends IReduxAction<Actions> {
    field: T;
    sentenceFragment: ISentenceFragmentReducerState;
    value: ISentenceFragmentReducerState[T];
}
