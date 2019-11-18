import { ComponentEventHandler } from '@root/components/Component._types';
import { IBookGlossEntity } from '@root/connectors/backend/IBookApi';

import { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';

export interface IProps {
    fragment?: IFragmentsReducerState;
    fragmentId: number;
    gloss: IBookGlossEntity;
    onFragmentMoveClick?: ComponentEventHandler<number>;
}
