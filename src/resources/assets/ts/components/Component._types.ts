export interface IDefaultComponent {
    props: Readonly<{
        id?: string;
        name?: string;
    }> | any;
}

/**
 * Represents a component event with `V` value type.
 */
export interface IComponentEvent<V> {
    name?: string;
    value: V;
}

export type ComponentEventHandler<T> = (ev: IComponentEvent<T>) => void;
