import {
    AnyAction,
} from 'redux';
import {
    ThunkAction,
    ThunkDispatch,
} from 'redux-thunk';

export interface IReduxAction<TType = string> {
    type: TType;
}

export type IReduxActionableState<T> = T & IReduxAction;

export type FirstArgument<T> = T extends (arg1: infer U, ...args: any[]) => any ? U : any;

export type CreateRootReducer<T> = {
    [R in keyof T]: FirstArgument<T[R]>;
};

export type ReduxThunk = ThunkAction<any, any, any, AnyAction>;
export type ReduxThunkDispatch = ThunkDispatch<any, any, any>;
