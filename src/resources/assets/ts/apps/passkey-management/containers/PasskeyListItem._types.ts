import type { IPasskey } from '../index._types';
import type { ComponentEventHandler } from '@root/components/Component._types';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';

export interface IProps {
    passkey: IPasskey;
    onDeleted: ComponentEventHandler<void>;
    passkeyApi?: IPasskeyApi;
}
