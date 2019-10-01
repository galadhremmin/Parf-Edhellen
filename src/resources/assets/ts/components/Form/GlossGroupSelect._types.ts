import BookApiConnector from '@root/connectors/backend/BookApiConnector';

import { IComponentProps } from './FormComponent._types';

export interface IProps extends IComponentProps<number> {
    apiConnector: BookApiConnector;
}
