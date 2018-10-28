import { Dispatch } from 'redux';

import BookApiConnector from '../../../connectors/backend/BookApiConnector';
import { IFindActionEntity } from '../../../connectors/backend/BookApiConnector.types';
import { stringHash } from '../../../utilities/func/hashing';
import { mapArray } from '../../../utilities/func/mapper';
import { Actions } from '../reducers/constants';
import { ISearchAction } from '../reducers/SearchReducer.types';
import {
    ISearchResult,
    ISelectSearchResultAction,
    ISetSearchResultAction,
} from '../reducers/SearchResultsReducer.types';

export default class SearchActions {
    constructor(private _api: BookApiConnector = new BookApiConnector()) {
    }

    public search(args: ISearchAction) {
        return async (dispatch: Dispatch) => {
            dispatch({
                type: Actions.RequestSearchResults,
                ...args,
            });

            let results: ISearchResult[] = [];
            if (typeof args.word === 'string' && args.word.length > 0) {
                try {
                    const rawResults = await this._api.find(args);

                    results = mapArray<IFindActionEntity, ISearchResult>({
                        id: (o) => stringHash(`${(o.ok || '')}.${o.k}`),
                        normalizedWord: 'nk',
                        originalWord: 'ok',
                        word: 'k',
                    }, rawResults);
                } catch (e) {
                    console.warn(e);
                }
            }

            dispatch(this.setSearchResults(results));
        };
    }

    public setSearchResults(searchResults: ISetSearchResultAction) {
        return {
            searchResults,
            type: Actions.ReceiveSearchResults,
        };
    }

    public selectSearchResult(searchResult: ISelectSearchResultAction) {
        return {
            id: searchResult.id,
            type: Actions.SelectSearchResult,
        };
    }
}
