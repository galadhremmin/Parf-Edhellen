import type { ILanguageEntity, ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import type { ISectionsState } from '../../reducers/SectionsReducer._types';

export interface IProps {
    languages: ILanguageEntity[];
    sections: ISectionsState<ILexicalEntryEntity>;
}
