import type { IPasskey } from '../index._types';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';

export interface IProps {
    passkeys: IPasskey[];
    onPasskeyDeleted: () => void;
    passkeyApi?: IPasskeyApi;
}
