import { ILanguageEntity } from '@root/connectors/backend/IBookApi';

export interface ILanguagesState {
    common: ILanguageEntity[];
    unusual: ILanguageEntity[];
    isEmpty: boolean;
}
