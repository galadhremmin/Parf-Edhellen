import React from 'react';
import { render } from 'react-dom';
import inject from './Injector';

import BookBrowserApp from './apps/book-browser';
import './index.scss';
render(<BookBrowserApp />, document.getElementById('ed-search-component'));

inject();
