import type { ComponentEventHandler } from '@root/components/Component._types';
import type { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';

import type { IFragmentChangeEventArgs } from './FragmentsGrid/FragmentsGrid._types';

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
