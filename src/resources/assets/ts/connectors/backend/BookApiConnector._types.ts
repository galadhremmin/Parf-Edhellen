export interface IFindActionRequest {
    includeOld?: boolean;
    languageId?: number;
    word?: string;
    reversed?: boolean;
}

export interface IFindActionEntity {
    k: string;
    nk: string;
    ok: string;
}

export type FindActionResponse = IFindActionEntity[];

export interface ILanguagesResponse {
    [period: string]: ILanguageEntity[];
}

export interface ILanguageEntity {
    id: number;
    name: string;
}
