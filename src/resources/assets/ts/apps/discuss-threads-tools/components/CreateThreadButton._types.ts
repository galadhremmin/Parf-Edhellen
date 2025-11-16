import type { ComponentEventHandler } from '@root/components/Component._types';
import type { ICreatePostRequest } from '@root/connectors/backend/IDiscussApi';
import ValidationError from '@root/connectors/ValidationError';

export interface IProps {
    error?: ValidationError;
    enabled?: boolean;
    groupId: number;
    groupName: string;
    onThreadCreate: ComponentEventHandler<ICreatePostRequest>;
}
