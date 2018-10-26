import {
    Dispatch,
} from 'redux';

import BookApiConnector, {
    IFindActionEntity,
} from '../../../connectors/backend/BookApiConnector';
import {
    mapArray,
} from '../../../utilities/func/mapper';
import {
    ISearchActionState,
    ISearchResultState,
} from '../_types';
import {
    Actions,
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
            const results = mapArray<IFindActionEntity, ISearchResultState>({
                normalizedWord: 'nk',
                originalWord: 'ok',
                word: 'k',
            }, rawResults);

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
