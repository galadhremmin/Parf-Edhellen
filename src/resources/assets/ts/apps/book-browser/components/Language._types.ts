import { IEventProps } from '../../../components/HtmlInject._types';
import {
    IGlossEntity,
    ILanguageEntity,
} from '../../../connectors/backend/BookApiConnector._types';

export interface IProps extends IEventProps {
    glosses: IGlossEntity[];
    language: ILanguageEntity;
}
