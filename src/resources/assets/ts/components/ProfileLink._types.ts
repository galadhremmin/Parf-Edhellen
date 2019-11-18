import { ReactNode } from 'react';

import { IAccountEntity } from '../connectors/backend/IBookApi';

export interface IProps {
    account: IAccountEntity;
    children?: ReactNode;
    className?: string;
}
