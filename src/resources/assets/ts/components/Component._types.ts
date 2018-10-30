export interface IComponentEvent<V> {
    name?: string;
    value: V;
}

export type ComponentEventHandler<T> = (ev: IComponentEvent<T>) => void;
