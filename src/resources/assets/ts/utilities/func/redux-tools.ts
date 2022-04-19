/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/no-unsafe-call */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */

import {
    compose,
} from 'redux';

export const composeEnhancers = (name: string): any => {
    const devTools = (window as any).__REDUX_DEVTOOLS_EXTENSION_COMPOSE__;
    return devTools ? devTools({ name: `ed-${name}-model` }) : compose;
};
