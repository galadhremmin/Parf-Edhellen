import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';

import { ISentenceFragmentErrorsReducerState } from '../reducers/SentenceFragmentErrorsReducer._types';
import { IFragmentChangeEventArgs } from './FragmentsGrid/FragmentsGrid._types';

export interface IFragmentFormEvents {
    onFragmentChange: ComponentEventHandler<IFragmentChangeEventArgs>;
    onParseTextRequest: ComponentEventHandler<string>;
    onTextChange: ComponentEventHandler<string>;
}

export interface IProps extends IFragmentFormEvents {
    errors: ISentenceFragmentErrorsReducerState;
    fragments: ISentenceFragmentEntity[];
    languageId: number;
    text: string;
    textIsDirty: boolean;
}
