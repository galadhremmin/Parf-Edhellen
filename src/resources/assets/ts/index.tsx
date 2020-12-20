import React from 'react';
import { render } from 'react-dom';

import { hookBootstrapToggles } from './utilities/BootstrapShims';

import BookBrowserApp from './apps/book-browser';
import inject from './Injector';

import './index.scss';
import DateLabel from './components/DateLabel';

/**
 * Render the website's most important component.
 */
const renderDictionary = () => {
    render(<BookBrowserApp />, document.getElementById('ed-search-component'));
};

const renderDates = () => {
    const dateElements = document.querySelectorAll<HTMLElement>('span.date');
    dateElements.forEach((dateElement) => {
        const date = dateElement.textContent.trim();
        if (date.length > 0) {
            render(<DateLabel dateTime={date} />, dateElement);
        }
    });
};

window.addEventListener('load', () => {
    renderDictionary();
    renderDates();
    inject();
    hookBootstrapToggles();
});
