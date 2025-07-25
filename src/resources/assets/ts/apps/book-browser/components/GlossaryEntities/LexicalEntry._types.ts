import { IEventProps } from '@root/components/HtmlInject._types';
import { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    bordered?: boolean;
    lexicalEntry: ILexicalEntryEntity;
    toolbar?: boolean;
    warnings?: boolean;
}
