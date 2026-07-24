import type { IEventProps } from '@root/components/HtmlInject._types';
import type { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    bordered?: boolean;
    demoted?: boolean;
    featured?: boolean;
    lexicalEntry: ILexicalEntryEntity;
    onPromoteFeatured?: () => void;
    toolbar?: boolean;
    warnings?: boolean;
}
