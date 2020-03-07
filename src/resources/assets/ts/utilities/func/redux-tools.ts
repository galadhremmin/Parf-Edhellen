import {
    compose,
} from 'redux';

export const composeEnhancers = (name: string): any =>
    (window as any).__REDUX_DEVTOOLS_EXTENSION_COMPOSE__({ name: `ed-${name}-model` }) || compose;
