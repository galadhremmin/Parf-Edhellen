export interface IInflection {
    groupName: string;
    id: number;
    name: string;
    isRestricted?: boolean;
}

export interface IInflectionMap {
    [groupName: string]: IInflection[];
}

export interface IUngweInflectionsRequest {
    lexicalEntryId: number;
}

export interface IUngweInflectionsResponse {
    words: Array<{
        qwid: string;
        lemma: string;
        homonym: string;
        forms: Array<{
            tag: string;
            forms: string[];
        }>;
    }>;
}

export interface IInflectionResourceApi {
    inflections(): Promise<IInflectionMap>;
    ungweInflections(args: IUngweInflectionsRequest): Promise<IUngweInflectionsResponse>;
}
