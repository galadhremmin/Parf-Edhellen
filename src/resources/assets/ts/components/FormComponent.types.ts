import {
    ComponentEventHandler,
} from './Component.types';

export interface IBackingComponentProps<V> {
    name?: string;
    tabIndex?: number;
    required?: boolean;
    value?: V;
}

export interface IComponentProps<V> extends IBackingComponentProps<V> {
    onChange?: ComponentEventHandler<V>;
}
