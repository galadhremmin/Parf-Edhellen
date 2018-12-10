import { ComponentEventHandler } from '@root/components/Component._types';
import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';

import { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';

export interface IProps {
    fragment?: IFragmentsReducerState;
    fragmentId: number;
    gloss: IGlossEntity;
    onFragmentMoveClick?: ComponentEventHandler<number>;
}
