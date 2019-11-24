import React from 'react';
import { render } from 'react-dom';

import { hookBootstrapToggles } from './utilities/BootstrapShims';

import BookBrowserApp from './apps/book-browser';
import inject from './Injector';

import './index.scss';
import Tengwar from './components/Tengwar';

/**
 * Render the website's most important component.
 */
const renderDictionary = () => {
    render(<BookBrowserApp />, document.getElementById('ed-search-component'));
};

renderDictionary();
inject();
hookBootstrapToggles();
