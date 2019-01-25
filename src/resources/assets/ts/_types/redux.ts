/// <reference path="./ts.d.ts" />

export interface IReduxAction {
    type: string;
}

export type IReduxActionableState<T> = T & IReduxAction;

export type DeriveRootReducer<T> = {
    [R in keyof T]: FirstArgument<T[R]>;
};
