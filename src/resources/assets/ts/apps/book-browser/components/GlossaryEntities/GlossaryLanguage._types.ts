import { IEventProps } from '@root/components/HtmlInject._types';
import {
    ILexicalEntryEntity,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    entries: ILexicalEntryEntity[];
    language: ILanguageEntity;
}
