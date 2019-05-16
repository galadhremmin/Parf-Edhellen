import React from 'react';

import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

const AccountSuggestion = (account: IAccountSuggestion) => {
    return <span>{account.nickname}</span>;
};

export default AccountSuggestion;
