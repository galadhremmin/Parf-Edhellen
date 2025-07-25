import { ILexicalEntryDetailEntity } from '@root/connectors/backend/IBookApi';
import { IProps as IParentProps } from './LexicalEntryDetails._types';

export interface IProps extends Pick<IParentProps, 'onReferenceLinkClick'> {
    detail: ILexicalEntryDetailEntity;
}
