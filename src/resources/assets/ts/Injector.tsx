import React, {
    Suspense,
} from 'react';
import { createRoot, hydrateRoot } from 'react-dom/client';
import Spinner from './components/Spinner';
import ErrorBoundary from './utilities/ErrorBoundary';
import { snakeCasePropsToCamelCase } from './utilities/func/snake-case';

const enum RenderMode {
    Async = 'async',
    Ssr = 'ssr',
}

/**
 * The `data-inject-module` attribute is used to identify elements that depend
 * on modules that must be loaded.
 */
const InjectModuleAttributeName = 'injectModule';

/**
 * The `data-inject-prop` attribute is used to inject properties to the loaded
 * module component.
 */
const InjectPropAttributeName = 'injectProp';

/**
 * Server-side rendered and thus only needs to be hydrated?
 */
const InjectModeAttributeName = 'injectMode';

/**
 * Loads the module `moduleName`'s default component and injects it to the
 * page.
 * @param element the element to inject the component into.
 * @param moduleName the module to load.
 * @param props properties to inject to the loaded component.
 */
const load = (element: HTMLElement, mode: RenderMode, moduleName: string, props: any) => {
    const Component = React.lazy(() => import(`./apps/${moduleName}/index`));

    switch (mode) {
        case RenderMode.Async: {
            const root = createRoot(element);
    
            root.render(<Suspense fallback={<Spinner />}>
                    <ErrorBoundary>
                        <React.StrictMode>
                            <Component {...props} />
                        </React.StrictMode>
                    </ErrorBoundary>
                </Suspense>);
            }
            break;
        case RenderMode.Ssr:
            hydrateRoot(element, <Component {...props} />);
            break;
    }
    
};

/**
 * Examines the components dataset to extract the properties configured to
 * be injected to the module component.
 * @param element the element with the dataset.
 */
const getProps = (element: HTMLElement) => Object.keys(element.dataset) //
    .filter((p: string) => p.indexOf(InjectPropAttributeName) === 0) //
    .reduce((ps: any, p: string) => {
        const propName = p.charAt(InjectPropAttributeName.length).toLowerCase() + //
        p.substr(InjectPropAttributeName.length + 1);

        let v = element.dataset[p];
        try {
            v = JSON.parse(v);
            v = snakeCasePropsToCamelCase(v);
        } catch (e) {
            // suppress errors.
        }

        return ({ ...ps, [propName]: v });
    }, {});

/**
 * Finds components within the current document that have the attribute
 * `data-inject-module`.
 */
const inject = () => {
    const elements = document.querySelectorAll('[data-inject-module]');
    for (let i = 0; i < elements.length; i += 1) {
        const element = elements.item(i) as HTMLElement;

        const moduleName = element.dataset[InjectModuleAttributeName];
        const mode = (element.dataset[InjectModeAttributeName] as RenderMode) || RenderMode.Async;
        const props = getProps(element);

        load(element, mode, moduleName, props);
    }
};

export default inject;
