import { IEventProps } from '@root/components/HtmlInject._types';
import { IBookGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps extends IEventProps {
    gloss: IBookGlossEntity;
    toolbar: boolean;
}
