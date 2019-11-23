import React, {
    useEffect,
    useState,
} from 'react';

import IAccountApi, { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';
import { DI, resolve } from '@root/di';
import { IProps } from './AccountValue._types';

const useFetch = (apiConnector: IAccountApi, accountId: number) => {
    const [ path, setPath ] = useState(null);

    useEffect(() => {
        apiConnector.getAvatar({
            accountId,
        }).then((a) => {
            setPath(a.avatar);
        });
    }, []);

    return path;
};

function AccountValue(props: IProps) {
    const {
        account,
        apiConnector,
    } = props;

    if (account === null) {
        return <span>No account</span>;
    }

    const avatar = useFetch(apiConnector, account.id);

    return <>
        {avatar && <img src={avatar} className="avatar" alt={account.nickname} />}
        {account.nickname}
        <span className="id">{account.id}</span>
    </>;
}

AccountValue.defaultProps = {
    apiConnector: resolve(DI.AccountApi),
} as Partial<IProps>;

export const injectAccountValue = (account: IAccountSuggestion) => <AccountValue account={account} />;

export default AccountValue;
