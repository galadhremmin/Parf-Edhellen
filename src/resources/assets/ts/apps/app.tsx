/* eslint-env node */
/* global context, dispatch */
declare global {
    const context: { props?: Record<string, any> };
    function dispatch(html: string): void;
}

import type { Attributes, ComponentClass, FunctionComponent } from 'react';
import { StrictMode } from 'react';
import { renderToString } from 'react-dom/server';
import setupContainer from '@root/di/config';
import { isNodeJs } from '@root/utilities/func/node';
import ErrorBoundary from '@root/utilities/ErrorBoundary';

export default function registerApp<P>(App: FunctionComponent<P> | ComponentClass<P>): FunctionComponent<P> | ComponentClass<P> {
    if (! isNodeJs()) {
        return App;
    }

    // Compile an initial state
    const props = context?.props || {};

    setupContainer();
    const render = renderToString(
        <ErrorBoundary>
            <StrictMode>
                <App {...(props as Attributes & P)} />
            </StrictMode>
        </ErrorBoundary>
    );

    dispatch(render);

    return App;
}
