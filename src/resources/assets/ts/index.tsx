import React from 'react';
import { render } from 'react-dom';

import './index.scss';

const load = async (element: HTMLElement, moduleName: string, props: any) => {
    const module = await import(`./apps/${moduleName}`);
    const Control = module.default;

    render(<Control {...props} />, element);
};

const InjectPropAttributeName = 'injectProp';
const elements = document.querySelectorAll('[data-inject-module]');
for (let i = 0; i < elements.length; i += 1) {
    const element = elements.item(i) as HTMLElement;

    const moduleName = element.dataset['injectModule'];
    const props = Object.keys(element.dataset) //
        .filter(prop => prop.startsWith(InjectPropAttributeName))
        .reduce((props: any, prop: string) => ({ ...props, [prop]: element.dataset[prop] }), {});

    load(element, moduleName, props);
}

import BookBrowserApp from './apps/book-browser';
render(<BookBrowserApp />, document.getElementById('ed-search-component'));
