import type { IEventProps } from '@root/components/HtmlInject._types';
import type { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    lexicalEntry: ILexicalEntryEntity;
    showDetails: boolean;
}
