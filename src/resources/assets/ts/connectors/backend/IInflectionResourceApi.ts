export interface IInflection {
    groupName: string;
    id: number;
    name: string;
    isRestricted?: boolean;
}

export interface IInflectionMap {
    [groupName: string]: IInflection[];
}

export interface IInflectionResourceApi {
    inflections(): Promise<IInflectionMap>;
}
