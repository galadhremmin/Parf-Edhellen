import {
    Dispatch,
} from 'redux';

import BookApiConnector, { ILanguageEntity } from '../../../connectors/backend/BookApiConnector';
import {
    Actions,
    ISearchActionState,
    ISearchResultState,
} from '../reducers/constants';

export default class SearchActions {
    constructor(private _api: BookApiConnector = new BookApiConnector()) {
    }

    public search(args: ISearchActionState) {
        return async (dispatch: Dispatch) => {
            dispatch({
                type: Actions.RequestSearchResults,
                ...args,
            });

            const rawResults = await this._api.find(args);
            const results = rawResults.map((r) => ({
                normalizedWord: r.nk,
                originalWord: r.ok,
                word: r.k,
            }));

            dispatch(this.setSearchResults(results));
        };
    }

    public setSearchResults(items: ISearchResultState[]) {
        return {
            items,
            type: Actions.ReceiveSearchResults,
        };
    }
}
