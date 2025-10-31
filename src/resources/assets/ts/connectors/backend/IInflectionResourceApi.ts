export interface IInflection {
    groupName: string;
    id: number;
    name: string;
    isRestricted?: boolean;
}

export interface IInflectionMap {
    [groupName: string]: IInflection[];
}

export interface IAutoInflectionsRequest {
    lexicalEntryId: number;
}

export interface IAutoInflectionsResponse {
    description: string;
    url: string;
    tengwarMode: string|null;
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
    autoInflections(args: IAutoInflectionsRequest): Promise<IAutoInflectionsResponse>;
}
