import type { IEventProps } from '@root/components/HtmlInject._types';
import type {
    ILexicalEntryEntity,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    entries: ILexicalEntryEntity[];
    featured?: boolean;
    language: ILanguageEntity;
    word: string;
}
