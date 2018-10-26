import React from 'react';
import { render } from 'react-dom';

import '../sass/app.scss';

import bookBrowserApp from './apps/book-browser';
render(bookBrowserApp, document.getElementById('ed-search-component'));
