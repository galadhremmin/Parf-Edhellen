export interface IComponentProps {
    id?: string;
    name?: string;
}

export interface IDefaultComponent {
    props: Readonly<IComponentProps> | any;
}

export type ComponentOrName = IDefaultComponent | string;

/**
 * Represents a component event with `V` value type.
 */
export interface IComponentEvent<V> {
    name?: string;
    value: V;
}

export type ComponentFiredEvent = boolean;
export type ComponentFiredEventAsync = Promise<boolean>;

export type ComponentEventHandler<T> = (ev: IComponentEvent<T>) => ComponentFiredEvent | Promise<void> | void;
