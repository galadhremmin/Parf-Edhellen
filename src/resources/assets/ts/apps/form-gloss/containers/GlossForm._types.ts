import { ComponentEventHandler } from '@root/components/Component._types';
import { IBookGlossEntity } from '@root/connectors/backend/BookApiConnector._types';
import { IWordEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';
import { IGlossState } from '../reducers/GlossReducer._types';

export interface IProps {
    gloss: IGlossState;
    name: string;
    onGlossFieldChange: ComponentEventHandler<IChangeSpec>;
    onSubmit: ComponentEventHandler<IBookGlossEntity>;
}

export type GlossProps = keyof IGlossState;

export interface IChangeSpec {
    field: GlossProps;
    value: any;
}
