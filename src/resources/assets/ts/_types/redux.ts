/// <reference path="./ts.d.ts" />

import {
    AnyAction,
} from 'redux';
import {
    ThunkAction,
    ThunkDispatch,
} from 'redux-thunk';

export interface IReduxAction {
    type: string;
}

export type IReduxActionableState<T> = T & IReduxAction;

export type CreateRootReducer<T> = {
    [R in keyof T]: FirstArgument<T[R]>;
};

export type ReduxThunk = ThunkAction<any, any, any, AnyAction>;
export type ReduxThunkDispatch = ThunkDispatch<any, any, any>;
