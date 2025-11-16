import type { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';

const AccountSuggestion = (account: IAccountSuggestion) => {
    return <span>{account.nickname}</span>;
};

export default AccountSuggestion;
