import { ComponentEventHandler } from '@root/components/Component._types';
import { RootReducer } from '../reducers';
import { IGlossState } from '../reducers/GlossReducer._types';
import { IInflectionGroupState } from '../reducers/InflectionsReducer._types';

export interface IProps extends Partial<Pick<RootReducer, 'changes' | 'errors' | 'gloss' | 'inflections'>> {
    confirmButton?: string;
    edit?: boolean;

    onCopyGloss?: ComponentEventHandler<void>;
    onGlossFieldChange?: ComponentEventHandler<IGlossFieldChangeArgs>;
    onInflectionCreate?: ComponentEventHandler<void>;
    onInflectionsChange?: ComponentEventHandler<IInflectionsChangeArgs>;
    onSubmit?: ComponentEventHandler<ISubmitArgs>;
}

export type GlossProps = keyof IGlossState;

export interface IGlossFieldChangeArgs {
    field: GlossProps;
    value: any;
}

export interface IInflectionsChangeArgs {
    inflectionGroupUuid: string;
    inflectionGroup: IInflectionGroupState;
}

export interface ISubmitArgs extends Partial<Pick<RootReducer, 'changes' | 'gloss' | 'inflections'>> {
}