import React from 'react';

import Avatar from '@root/components/Avatar';
import { IProps } from '../index._types';

import './AccountList.scss';

function AccountList(props: IProps) {
    const {
        accounts,
    } = props;

    return <div className="AccountList--avatars">
        {accounts.map((account) => <Avatar key={account.id}
            title={account.nickname}
            path={account.avatarPath}
        />)}
    </div>;
}

AccountList.defaultProps = {
    accounts: [],
} as Partial<IProps>;

export default AccountList;
