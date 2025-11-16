import type { ReactNode } from 'react';

import type { IAccountEntity } from '../connectors/backend/IGlossResourceApi';

export interface IProps {
    account: IAccountEntity;
    children?: ReactNode;
    className?: string;
}
