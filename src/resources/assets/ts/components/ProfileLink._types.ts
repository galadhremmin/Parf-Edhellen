import { ReactNode } from 'react';

import { IAccountEntity } from '../connectors/backend/BookApiConnector._types';

export interface IProps {
    account: IAccountEntity;
    children?: ReactNode;
    className?: string;
}