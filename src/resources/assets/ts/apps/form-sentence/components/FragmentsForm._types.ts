import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';

import { IFragmentChangeEventArgs } from './FragmentsGrid/FragmentsGrid._types';

export interface IFragmentFormEvents {
    onFragmentChange: ComponentEventHandler<IFragmentChangeEventArgs>;
    onParseTextRequest: ComponentEventHandler<string>;
    onTextChange: ComponentEventHandler<string>;
}

export interface IProps extends IFragmentFormEvents {
    fragments: ISentenceFragmentEntity[];
    languageId: number;
    text: string;
    textIsDirty: boolean;
}
