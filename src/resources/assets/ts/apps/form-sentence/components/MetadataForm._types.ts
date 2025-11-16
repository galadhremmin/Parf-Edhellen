import type { ComponentEventHandler } from '@root/components/Component._types';
import type { ISentenceFieldChangeSpec } from '../containers/SentenceForm._types';
import type { ISentenceReducerState } from '../reducers/SentenceReducer._types';

export interface IMetadataFormEvents {
    onMetadataChange?: ComponentEventHandler<ISentenceFieldChangeSpec>;
}

export interface IProps extends IMetadataFormEvents {
    sentence: ISentenceReducerState;
}
