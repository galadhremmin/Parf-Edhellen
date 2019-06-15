import { ComponentEventHandler } from '@root/components/Component._types';
import { IGlossEntity } from '@root/connectors/backend/GlossResourceApiConnector._types';
import { IGlossState } from '../reducers/GlossReducer._types';

export interface IProps {
    confirmButton?: string;
    edit?: boolean;
    gloss: IGlossState;
    name: string;
    onGlossFieldChange: ComponentEventHandler<IChangeSpec>;
    onSubmit: ComponentEventHandler<IGlossEntity>;
}

export type GlossProps = keyof IGlossState;

export interface IChangeSpec {
    field: GlossProps;
    value: any;
}
