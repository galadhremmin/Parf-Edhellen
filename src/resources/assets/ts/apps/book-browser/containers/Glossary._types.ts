import { ILanguageEntity } from '../../../connectors/backend/BookApiConnector._types';
import { IGlossaryState } from '../reducers/GlossaryReducer._types';
import { IGlossesState } from '../reducers/GlossesReducer._types';

export interface IProps extends IGlossaryState {
    glosses: IGlossesState;
    languages: ILanguageEntity[];
    unusualLanguages: ILanguageEntity[];
    isEmpty: boolean;
}
