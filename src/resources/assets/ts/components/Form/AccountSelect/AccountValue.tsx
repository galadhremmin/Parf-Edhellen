import {
    useEffect,
    useState,
} from 'react';

import IAccountApi, { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { IProps } from './AccountValue._types';

const useFetch = (apiConnector: IAccountApi, accountId: number) => {
    const [ path, setPath ] = useState<string>(null);

    useEffect(() => {
        if (! accountId) {
            setPath(null);
        } else {
            apiConnector.getAvatar({
                accountId,
            }).then((a) => {
                setPath(a.avatar);
            }).catch(() => {
                setPath(null);
            });
        }
    }, [accountId]);

    return path;
};

function AccountValue(props: IProps) {
    const {
        account,
        apiConnector = resolve(DI.AccountApi),
    } = props;

    const avatar = useFetch(apiConnector, account?.id);

    if (account === null) {
        return <span>No account</span>;
    }

    return <>
        {avatar && <img src={avatar} className="avatar" alt={account.nickname} />}
        {account.nickname}
        <span className="id">{account.id}</span>
    </>;
}

export const injectAccountValue = (account: IAccountSuggestion) => <AccountValue account={account} />;

export default AccountValue;
