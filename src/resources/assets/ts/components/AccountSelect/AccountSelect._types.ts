import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';

import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps {
    apiConnector: AccountApiConnector;
}
