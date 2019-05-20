import { ComponentEventHandler } from '@root/components/Component._types';
import { IGlossEntity } from '@root/connectors/backend/BookApiConnector._types';
import { IGlossState } from '../reducers/GlossReducer._types';

export interface IProps {
    gloss: IGlossState;
    onSubmit: ComponentEventHandler<IGlossEntity>;
}
