import type { UnknownAction } from 'redux';
import type {
    ThunkAction,
    ThunkDispatch,
} from 'redux-thunk';

export type Reducer<TState, TAction extends IReduxAction> = (state: TState, action: TAction) => TState;

export interface IReduxAction<TType = string> {
    type: TType;
}

export type IReduxActionableState<T> = T & IReduxAction;

export type FirstArgument<T> = T extends (arg1: infer U, ...args: any[]) => any ? U : any;

export type CreateRootReducer<T> = {
    [R in keyof T]: FirstArgument<T[R]>;
};

export type ReduxThunk = ThunkAction<any, any, any, UnknownAction>;
export type ReduxThunkDispatch = ThunkDispatch<any, any, any>;
