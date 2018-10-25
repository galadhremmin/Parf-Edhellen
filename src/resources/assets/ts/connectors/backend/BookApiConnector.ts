import ApiConnector from '../ApiConnector';

export default class BookApiConnector {
    constructor(private _api = new ApiConnector()) {
    }

    public async find(args: IFindActionRequest) {
        const response = await this._api.post<FindActionResponse>('book/find', args);
        return response;
    }

    public async languages() {
        const response = await this._api.get<ILanguagesResponse>('book/languages');
        return response;
    }
}

export interface IFindActionRequest {
    includeOld?: boolean;
    languageId?: number;
    query?: string;
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
