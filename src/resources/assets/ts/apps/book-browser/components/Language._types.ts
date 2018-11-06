import { IEventProps } from '@root/components/HtmlInject._types';
import {
    IGlossEntity,
    ILanguageEntity,
} from '@root/connectors/backend/BookApiConnector._types';

export interface IProps extends IEventProps {
    glosses: IGlossEntity[];
    language: ILanguageEntity;
}
