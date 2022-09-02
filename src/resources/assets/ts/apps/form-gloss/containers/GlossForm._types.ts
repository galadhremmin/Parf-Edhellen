import { ComponentEventHandler } from '@root/components/Component._types';
import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';
import ValidationError from '@root/connectors/ValidationError';
import { IGlossState } from '../reducers/GlossReducer._types';

export interface IProps {
    confirmButton?: string;
    edit?: boolean;
    errors?: ValidationError;
    gloss?: IGlossState;
    inflections?: IGlossInflection[];
    name?: string;
    onEditChange?: ComponentEventHandler<number>;
    onGlossFieldChange?: ComponentEventHandler<IChangeSpec>;
    onSubmit?: ComponentEventHandler<IGlossEntity>;
}

export type GlossProps = keyof IGlossState;

export interface IChangeSpec {
    field: GlossProps;
    value: any;
}
