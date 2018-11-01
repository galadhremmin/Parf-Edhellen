import {
    IGlossEntity,
    ILanguageEntity,
} from '../../../connectors/backend/BookApiConnector._types';

export interface IProps {
    glosses: IGlossEntity[];
    language: ILanguageEntity;
}
