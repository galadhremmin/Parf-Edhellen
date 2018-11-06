import { ILanguageEntity } from '@root/connectors/backend/BookApiConnector._types';

export interface ILanguagesState {
    common: ILanguageEntity[];
    unusual: ILanguageEntity[];
    isEmpty: boolean;
}
