import { IEventProps } from '@root/components/HtmlInject._types';
import {
    IBookGlossEntity,
    ILanguageEntity,
} from '@root/connectors/backend/IBookApi';

export interface IProps extends IEventProps {
    glosses: IBookGlossEntity[];
    language: ILanguageEntity;
}
