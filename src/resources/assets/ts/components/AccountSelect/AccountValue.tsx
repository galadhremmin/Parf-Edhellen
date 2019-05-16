import React from 'react';

import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

const AccountValue = (account: IAccountSuggestion) => <span>
    {account ? `${account.nickname} (${account.id})` : 'No account'}
</span>;

export default AccountValue;
