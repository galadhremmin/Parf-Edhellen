import type { IAccount } from '../index._types';
import type { ComponentEventHandler } from '@root/components/Component._types';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';

export interface IProps {
    account: IAccount;
    passkeyApi?: IPasskeyApi;
    existingPasskeys?: Array<{ displayName: string }>;
    onSuccess: ComponentEventHandler<void>;
    onCancel: ComponentEventHandler<void>;
    formRef?: React.RefObject<HTMLFormElement>;
    onValidationChange?: ComponentEventHandler<boolean>;
}
