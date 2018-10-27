import React from 'react';

import {
    ComponentEventHandler,
} from '../../../components/Component.types';
import {
    ISearchResult,
} from '../reducers/SearchResultsReducer.types';

export interface IProps {
    searchResult: ISearchResult;
    onClick: ComponentEventHandler<ISearchResult>;
}
