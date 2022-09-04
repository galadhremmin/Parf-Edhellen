import { IProps as IParentProps } from '../containers/MasterForm._types';
import { IGlossState } from '../reducers/GlossReducer._types';

export interface IProps extends Pick<IParentProps, 'onGlossFieldChange'> {
    gloss: IGlossState;
    name: string;
}
