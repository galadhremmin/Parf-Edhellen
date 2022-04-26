import {
    IReduxAction,
    Reducer,
} from '@root/_types';

const collectivize = <TState, TAction extends IReduxAction>(reducer: Reducer<TState, TAction>, qualifier: (entity: TState, action: TAction) => boolean, eventNames: string[]): Reducer<TState[], TAction> => //
    (state: TState[] = [], action: TAction) => {
        if (eventNames.includes(action.type)) {
            const elementState = state.find(s => qualifier(s, action));
            return state.filter(s => s !== elementState)
                .concat(reducer(elementState, action));
        }

        return state;
    };

export default collectivize;
