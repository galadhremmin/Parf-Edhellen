import { IEventProps } from '@root/components/HtmlInject._types';
import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps extends IEventProps {
    gloss: IGlossEntity;
    toolbar: boolean;
}
