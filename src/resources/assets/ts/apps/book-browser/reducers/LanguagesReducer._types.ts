import { ILanguageEntity } from '../../../connectors/backend/BookApiConnector._types';

export interface ILanguagesState {
    common: ILanguageEntity[];
    unusual: ILanguageEntity[];
    isEmpty: boolean;
}
