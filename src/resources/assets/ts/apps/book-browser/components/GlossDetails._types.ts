import { IEventProps } from '../../../components/HtmlInject._types';
import { IGlossEntity } from '../../../connectors/backend/BookApiConnector._types';

export interface IProps extends IEventProps {
    gloss: IGlossEntity;
    showDetails: boolean;
}
