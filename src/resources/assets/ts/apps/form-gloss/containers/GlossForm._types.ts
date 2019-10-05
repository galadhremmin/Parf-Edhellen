import { ComponentEventHandler } from '@root/components/Component._types';
import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';
import ValidationError from '@root/connectors/ValidationError';
import { IGlossState } from '../reducers/GlossReducer._types';

export interface IProps {
    confirmButton?: string;
    edit?: boolean;
    errors: ValidationError;
    gloss: IGlossState;
    name: string;
    onEditChange: ComponentEventHandler<number>;
    onGlossFieldChange: ComponentEventHandler<IChangeSpec>;
    onSubmit: ComponentEventHandler<IGlossEntity>;
}

export type GlossProps = keyof IGlossState;

export interface IChangeSpec {
    field: GlossProps;
    value: any;
}
