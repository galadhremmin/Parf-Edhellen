import {
    injectable,
} from 'inversify';
import {
    Dispatch,
} from 'redux';
import 'reflect-metadata';

import ApiConnector from '../../../connectors/ApiConnector';
import {
    Actions,
    ISearchActionState,
    ISearchResultState,
} from '../reducers/constants';

@injectable()
export default class SearchActions {
    constructor(private _api: ApiConnector) {
    }

    public search(args: ISearchActionState) {
        return async (dispatch: Dispatch) => {
            dispatch({
                type: Actions.RequestSearchResults,
                ...args,
            });

            const rawResults = await this._api.post<any[]>('book/find', args);
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
