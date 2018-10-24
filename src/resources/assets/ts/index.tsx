import React from 'react';
import { render } from 'react-dom';

import '../sass/app.scss';

import SearchContainer from './apps/book-browser/components/SearchContainer';

render(<SearchContainer />, document.getElementById('ed-search-component'));
