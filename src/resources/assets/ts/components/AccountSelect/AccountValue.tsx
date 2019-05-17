import React, {
    useEffect,
    useState,
} from 'react';

import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';
import SharedReference from '@root/utilities/SharedReference';
import { IProps } from './AccountValue._types';

const useFetch = (apiConnector: AccountApiConnector, accountId: number) => {
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
    apiConnector: SharedReference.getInstance(AccountApiConnector),
} as Partial<IProps>;

export const injectAccountValue = (account: IAccountSuggestion) => <AccountValue account={account} />;

export default AccountValue;
