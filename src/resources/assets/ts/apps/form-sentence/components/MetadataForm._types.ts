import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFieldChangeSpec } from '../containers/SentenceForm._types';
import { ISentenceReducerState } from '../reducers/SentenceReducer._types';

export interface IMetadataFormEvents {
    onMetadataChange?: ComponentEventHandler<ISentenceFieldChangeSpec>;
}

export interface IProps extends IMetadataFormEvents {
    sentence: ISentenceReducerState;
}
