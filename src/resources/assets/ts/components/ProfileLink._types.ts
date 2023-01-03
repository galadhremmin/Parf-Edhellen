import { ReactNode } from 'react';

import { IAccountEntity } from '../connectors/backend/IGlossResourceApi';

export interface IProps {
    account: IAccountEntity;
    children?: ReactNode;
    className?: string;
}
