export interface IAccount {
    id: number;
    email: string;
    nickname: string;
}

export interface IPasskey {
    id: number;
    displayName: string;
    createdAt: string;
    lastUsedAt: string | null;
    transport: string;
    counter: number;
    aaguid: string | null;
}

export interface IProps {
    account: IAccount;
}
