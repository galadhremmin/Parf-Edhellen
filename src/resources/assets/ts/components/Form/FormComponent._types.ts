import {
    ComponentEventHandler,
} from '../Component._types';

export interface IBackingComponentProps<V> {
    className?: string;
    name?: string;
    tabIndex?: number;
    required?: boolean;
    value?: V;
}

export interface IComponentProps<V = any> extends IBackingComponentProps<V> {
    onChange?: ComponentEventHandler<V>;
}
