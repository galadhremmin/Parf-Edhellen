import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';

import { IProps as IToolbarProps } from '../../containers/Toolbar._types';

export interface IProps extends IToolbarProps {
    apiConnector: DiscussApiConnector;
}
