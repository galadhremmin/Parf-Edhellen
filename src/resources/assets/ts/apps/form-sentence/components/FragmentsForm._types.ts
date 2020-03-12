import { ComponentEventHandler } from '@root/components/Component._types';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';

export interface IProps {
    fragments: ISentenceFragmentEntity[];
    onChange: ComponentEventHandler<string>;
    text: string;
}
