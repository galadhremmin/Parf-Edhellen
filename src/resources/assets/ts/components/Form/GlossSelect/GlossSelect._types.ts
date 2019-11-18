import IBookApiConnector from '@root/connectors/backend/IBookApi';

import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<number> {
    apiConnector?: IBookApiConnector;
}
