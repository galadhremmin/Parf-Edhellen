export interface IAccount {
    avatarPath: string;
    hasAvatar: boolean;
    id: number;
    nickname: string;
}

export interface IProps {
    accounts: IAccount[];
}
