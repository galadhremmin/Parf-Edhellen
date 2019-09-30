import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import { ISuggestionEntity } from '@root/connectors/backend/BookApiConnector._types';

import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<number> {
    apiConnector?: BookApiConnector;
}
