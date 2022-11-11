import { ISubscriptionApi } from '@root/connectors/backend/ISubscriptionApi';

export interface IProps {
    className?: string;
    groupId: number;
    subscriptionApi: ISubscriptionApi;
}
