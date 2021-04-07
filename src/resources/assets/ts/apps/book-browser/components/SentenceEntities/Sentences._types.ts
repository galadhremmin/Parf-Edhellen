import { ILanguageEntity, ISentenceEntity } from '@root/connectors/backend/IBookApi';

export interface IProps {
    language: ILanguageEntity;
    sentences: ISentenceEntity[];
}
