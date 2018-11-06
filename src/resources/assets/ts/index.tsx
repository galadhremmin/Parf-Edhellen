import React from 'react';
import { render } from 'react-dom';

import './index.scss';

import bookBrowserApp from './apps/book-browser';
render(bookBrowserApp, document.getElementById('ed-search-component'));

const injections = document.querySelectorAll('[data-inject-module]');
for (let i = 0; i < injections.length; i += 1) {
    let injection = injections.item(i);

    console.log(injection);
}
