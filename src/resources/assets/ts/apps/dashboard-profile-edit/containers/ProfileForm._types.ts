import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import { IAccountEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface IProps {
    account: IAccountEntity;
    api: AccountApiConnector;
}
