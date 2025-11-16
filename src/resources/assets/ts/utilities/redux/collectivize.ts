import type {
    IReduxAction,
    Reducer,
} from '@root/_types';

export const DEFAULT_COLLECTIVIZE_KEY = '__reducer_default';

const collectivize = <TState, TAction extends IReduxAction>(reducer: Reducer<TState, TAction>, keyGenerator: (action: TAction) => string | null, eventNames: string[]): Reducer<Record<string, TState>, TAction> => //
    (state: Record<string, TState> = {
        [DEFAULT_COLLECTIVIZE_KEY]: reducer(undefined, { type: '' } as never),
    }, action: TAction) => {
        if (eventNames.includes(action.type)) {
            const key = keyGenerator(action);
            if (key !== null) {
                return {
                    ...state,
                    [key]: reducer(state[key], action),
                };
            }
        }

        return state;
    };

export const getStateOrDefault = <TState>(state: Record<string, TState>, key: string) => //
    state[key] === undefined ? state[DEFAULT_COLLECTIVIZE_KEY] : state[key];

export default collectivize;
