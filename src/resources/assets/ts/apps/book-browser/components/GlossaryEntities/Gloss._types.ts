import { IEventProps } from '@root/components/HtmlInject._types';
import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    bordered?: boolean;
    gloss: IBookGlossEntity;
    toolbar: boolean;
    warnings?: boolean;
}
