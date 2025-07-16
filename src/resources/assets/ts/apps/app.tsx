declare global {
    var context: { props?: Record<string, any> };
    function dispatch(html: string): void;
}

import ReactDOMServer from 'react-dom/server';
import setupContainer from '@root/di/config';
import { isNodeJs } from '@root/utilities/func/node';
import { Attributes, ComponentClass, FunctionComponent, StrictMode } from 'react';
import ErrorBoundary from '@root/utilities/ErrorBoundary';

export default function registerApp<P>(App: FunctionComponent<P> | ComponentClass<P>): FunctionComponent<P> | ComponentClass<P> {
    if (! isNodeJs()) {
        return App;
    }

    // Compile an initial state
    const props = globalThis.context?.props || {};

    setupContainer();
    const render = ReactDOMServer.renderToString(
        <ErrorBoundary>
            <StrictMode>
                <App {...(props as Attributes & P)} />
            </StrictMode>
        </ErrorBoundary>
    );

    (globalThis.dispatch || function (_: string) {})(render);

    return App;
}
