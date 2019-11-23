import IDiscussApi from '@root/connectors/backend/IDiscussApi';

import { IProps as IToolbarProps } from './index._types';

export interface IProps extends IToolbarProps {
    apiConnector?: IDiscussApi;
}
