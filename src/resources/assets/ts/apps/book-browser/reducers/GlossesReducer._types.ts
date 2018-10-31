import { IGlossEntity } from '../../../connectors/backend/BookApiConnector._types';

export interface IGlossesState {
    [ languageId: number ]: IGlossEntity[];
}
