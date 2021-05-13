import { IBookGlossDetailEntity } from '@root/connectors/backend/IBookApi';
import { IProps as IParentProps } from './GlossDetails._types';

export interface IProps extends Pick<IParentProps, 'onReferenceLinkClick'> {
    detail: IBookGlossDetailEntity;
}
