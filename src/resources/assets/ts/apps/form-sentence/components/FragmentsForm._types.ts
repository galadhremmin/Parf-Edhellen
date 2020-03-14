import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';

import { IFragmentChangeEventArgs } from './FragmentsGrid/FragmentsGrid._types';

export interface IProps {
    fragments: ISentenceFragmentEntity[];
    onFragmentChange: ComponentEventHandler<IFragmentChangeEventArgs>;
    onTextChange: ComponentEventHandler<string>;
    text: string;
}
