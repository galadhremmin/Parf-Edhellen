export interface IReduxAction {
    type: string;
}

export type IReduxActionableState<T> = T & IReduxAction;
