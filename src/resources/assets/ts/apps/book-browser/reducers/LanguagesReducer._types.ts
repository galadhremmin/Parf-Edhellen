import { IReduxAction } from '../../../_types';
import { ILanguageEntity } from '../../../connectors/backend/BookApiConnector._types';

export type LanguagesState = ILanguageEntity[];

export interface ILanguagesAction extends IReduxAction {
    languages: ILanguageEntity[];
}
